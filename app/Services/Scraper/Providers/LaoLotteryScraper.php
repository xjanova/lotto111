<?php

namespace App\Services\Scraper\Providers;

use App\Models\ResultSource;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\ScraperResult;
use Illuminate\Support\Facades\Log;

/**
 * หวยลาว (Lao Lottery / ลาวพัฒนา)
 *
 * Primary:  lotto.mthai.com - Thai media site, stable HTML
 * Fallback: ดึงจาก fallback_url ที่ตั้งใน config
 *
 * ออกรางวัล: จันทร์, พุธ, ศุกร์ เวลา ~20:00-20:30
 *
 * ผลหวยลาว: เลข 6 หลัก → ตัดเป็น 5/4/3/2/1 หลัก
 *   - three_top = 3 หลักท้าย
 *   - two_bottom = 2 หลักท้าย
 */
class LaoLotteryScraper extends AbstractScraper
{
    public function getProviderName(): string
    {
        return 'lao_lottery';
    }

    public function getSupportedSlugs(): array
    {
        return ['laos', 'laos-set'];
    }

    public function fetch(ResultSource $source, ?string $date = null): ScraperResult
    {
        $url = $source->source_url ?: 'https://lotto.mthai.com/lottery/lao';

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($url, $source) {
                return $this->httpGet($url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return $this->tryFallback($source, $date, "Primary returned HTTP {$response->status()}");
            }

            $html = $response->body();
            $result = $this->parseHtmlResult($html, $url, $elapsed, $date);

            if ($result->success) {
                return $result;
            }

            return $this->tryFallback($source, $date, $result->error ?? 'Parse failed');
        } catch (\Throwable $e) {
            return $this->tryFallback($source, $date, $e->getMessage());
        }
    }

    /**
     * Parse HTML จาก mthai.com
     */
    private function parseHtmlResult(string $html, string $url, int $elapsed, ?string $date = null): ScraperResult
    {
        $dom = $this->parseHtml($html);

        // mthai.com lottery pages use structured div elements with result numbers
        // Pattern: look for 6-digit lottery number in result section
        $fullNumber = null;
        $drawDate = null;

        // Try multiple selectors - mthai changes layout occasionally
        $selectors = [
            "//div[contains(@class, 'lottery-result')]//span[contains(@class, 'number')]",
            "//div[contains(@class, 'result')]//div[contains(@class, 'num')]",
            "//table[contains(@class, 'result')]//td[contains(@class, 'number')]",
            "//div[contains(@class, 'lotto-number')]",
            "//h2[contains(@class, 'prize')]",
            "//div[contains(@class, 'prize-number')]",
        ];

        foreach ($selectors as $selector) {
            $nodes = $this->xpath($dom, $selector);
            if ($nodes->length > 0) {
                $text = $this->cleanNumber($nodes->item(0)->textContent);
                if (strlen($text) === 6 && ctype_digit($text)) {
                    $fullNumber = $text;
                    break;
                }
                // Try combining digits from multiple nodes (some layouts split digits)
                if ($nodes->length >= 6) {
                    $combined = '';
                    for ($i = 0; $i < min($nodes->length, 6); $i++) {
                        $combined .= $this->cleanNumber($nodes->item($i)->textContent);
                    }
                    if (strlen($combined) === 6 && ctype_digit($combined)) {
                        $fullNumber = $combined;
                        break;
                    }
                }
            }
        }

        // Fallback: scan for 6-digit patterns in the HTML
        if (! $fullNumber) {
            if (preg_match('/ผลหวยลาว[^0-9]*(\d{6})/u', $html, $m)) {
                $fullNumber = $m[1];
            } elseif (preg_match('/รางวัลที่\s*1[^0-9]*(\d{6})/u', $html, $m)) {
                $fullNumber = $m[1];
            } elseif (preg_match('/เลข\s*6\s*ตัว[^0-9]*(\d{6})/u', $html, $m)) {
                $fullNumber = $m[1];
            }
        }

        // Try to extract draw date
        if (preg_match('/(\d{1,2})\s*(ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.)\s*(\d{2,4})/u', $html, $dateMatch)) {
            $drawDate = $this->parseThaiDate($dateMatch[1], $dateMatch[2], $dateMatch[3]);
        }

        if (! $fullNumber || strlen($fullNumber) < 3) {
            return ScraperResult::noData($url);
        }

        // ถ้า date filter ระบุมา ให้เช็คว่าตรงกันไหม
        if ($date && $drawDate && $drawDate !== $date) {
            return ScraperResult::noData($url);
        }

        // Map full number to standard results
        $results = [
            'full_number' => $fullNumber,
            'three_top' => substr($fullNumber, -3),
            'two_top' => substr($fullNumber, -2),
            'two_bottom' => substr($fullNumber, -2), // ลาวใช้ 2 หลักท้ายเหมือนกัน
        ];

        // ถ้ามีเลข 6 หลัก ตัดแบ่งเพิ่ม
        if (strlen($fullNumber) === 6) {
            $results['five_digits'] = substr($fullNumber, -5);
            $results['four_digits'] = substr($fullNumber, -4);
        }

        return ScraperResult::success(
            results: $results,
            rawData: ['full_number' => $fullNumber, 'html_length' => strlen($html)],
            drawDate: $drawDate ?? $date ?? date('Y-m-d'),
            sourceUrl: $url,
            responseTimeMs: $elapsed,
        );
    }

    /**
     * ลอง fallback URL
     */
    private function tryFallback(ResultSource $source, ?string $date, string $primaryError): ScraperResult
    {
        if (empty($source->fallback_url)) {
            return ScraperResult::failed("Primary failed: {$primaryError}, no fallback configured");
        }

        Log::info("LaoLottery: Primary failed ({$primaryError}), trying fallback: {$source->fallback_url}");

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($source) {
                return $this->httpGet($source->fallback_url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return ScraperResult::failed("Both sources failed. Fallback HTTP {$response->status()}", $source->fallback_url, $elapsed);
            }

            return $this->parseHtmlResult($response->body(), $source->fallback_url, $elapsed, $date);
        } catch (\Throwable $e) {
            return ScraperResult::failed("Both sources failed. Fallback: {$e->getMessage()}");
        }
    }

    /**
     * แปลงวันที่ภาษาไทยเป็น Y-m-d
     */
    private function parseThaiDate(string $day, string $thaiMonth, string $year): ?string
    {
        $months = [
            'ม.ค.' => 1, 'ก.พ.' => 2, 'มี.ค.' => 3, 'เม.ย.' => 4,
            'พ.ค.' => 5, 'มิ.ย.' => 6, 'ก.ค.' => 7, 'ส.ค.' => 8,
            'ก.ย.' => 9, 'ต.ค.' => 10, 'พ.ย.' => 11, 'ธ.ค.' => 12,
        ];

        $month = $months[$thaiMonth] ?? null;
        if (! $month) {
            return null;
        }

        $y = (int) $year;
        // แปลง พ.ศ. เป็น ค.ศ.
        if ($y > 2500) {
            $y -= 543;
        }
        // ถ้าเป็น 2 หลัก
        if ($y < 100) {
            $y += ($y > 50) ? 1900 : 2000;
        }

        return sprintf('%04d-%02d-%02d', $y, $month, (int) $day);
    }
}
