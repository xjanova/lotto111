<?php

namespace App\Services\Scraper\Providers;

use App\Models\ResultSource;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\ScraperResult;
use Illuminate\Support\Facades\Log;

/**
 * หวยมาเลย์ (Malaysia 4D / Grand Dragon Lotto)
 *
 * Primary:  GD Lotto (Grand Dragon) - ออกทุกวัน ~19:10 (GMT+8)
 * Fallback: check4d.org - aggregator สำหรับ Magnum/Toto/DaMaCai
 *
 * ผลรางวัล 4D format:
 *   - 1st Prize: 4 หลัก → three_top = 3 ท้าย, two_top = 2 ท้าย
 *   - 2nd Prize: 4 หลัก
 *   - 3rd Prize: 4 หลัก → two_bottom = 2 ท้าย
 *   - Special & Consolation prizes
 *
 * ออกรางวัล:
 *   - GD Lotto: ทุกวัน ~19:10 (GMT+8) = 18:10 (GMT+7)
 *   - Magnum/Toto/DaMaCai: พุธ, เสาร์, อาทิตย์
 */
class MalaysiaLotteryScraper extends AbstractScraper
{
    public function getProviderName(): string
    {
        return 'malaysia_lottery';
    }

    public function getSupportedSlugs(): array
    {
        return ['malaysia', 'malaysia-set'];
    }

    public function fetch(ResultSource $source, ?string $date = null): ScraperResult
    {
        // ลองจาก GD Lotto ก่อน (ออกทุกวัน)
        $result = $this->fetchFromGdLotto($source, $date);
        if ($result->success) {
            return $result;
        }

        // ลอง check4d.org
        return $this->fetchFromCheck4d($source, $date);
    }

    /**
     * ดึงจาก GD Lotto
     */
    private function fetchFromGdLotto(ResultSource $source, ?string $date = null): ScraperResult
    {
        $url = $source->source_url ?: 'https://4dno.org/en/';

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($url, $source) {
                return $this->httpGet($url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return ScraperResult::failed("GD Lotto HTTP {$response->status()}", $url, $elapsed);
            }

            return $this->parse4dResult($response->body(), $url, $elapsed, $date);
        } catch (\Throwable $e) {
            return ScraperResult::failed("GD Lotto error: {$e->getMessage()}", $url);
        }
    }

    /**
     * ดึงจาก check4d.org
     */
    private function fetchFromCheck4d(ResultSource $source, ?string $date = null): ScraperResult
    {
        $url = $source->fallback_url ?: 'https://www.check4d.org/';

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($url, $source) {
                return $this->httpGet($url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return ScraperResult::failed("check4d HTTP {$response->status()}", $url, $elapsed);
            }

            return $this->parse4dResult($response->body(), $url, $elapsed, $date);
        } catch (\Throwable $e) {
            return ScraperResult::failed("check4d error: {$e->getMessage()}", $url);
        }
    }

    /**
     * Parse 4D results from HTML
     */
    private function parse4dResult(string $html, string $url, int $elapsed, ?string $date = null): ScraperResult
    {
        $dom = $this->parseHtml($html);

        $firstPrize = null;
        $secondPrize = null;
        $thirdPrize = null;
        $drawDate = null;

        // Try structured selectors
        $selectors = [
            "//td[contains(@class, '1st')]",
            "//div[contains(@class, 'prize')]//span",
            "//table[contains(@class, 'result')]//td",
        ];

        foreach ($selectors as $selector) {
            $nodes = $this->xpath($dom, $selector);
            if ($nodes->length >= 3) {
                $firstPrize = $this->cleanNumber($nodes->item(0)->textContent);
                $secondPrize = $this->cleanNumber($nodes->item(1)->textContent);
                $thirdPrize = $this->cleanNumber($nodes->item(2)->textContent);
                if (strlen($firstPrize) === 4) {
                    break;
                }
                // Reset if not valid 4D
                $firstPrize = $secondPrize = $thirdPrize = null;
            }
        }

        // Regex fallback for 4D prize patterns
        if (! $firstPrize) {
            // Pattern: "1st Prize" or "1st" followed by 4-digit number
            if (preg_match('/1st\s*(?:Prize)?[^0-9]*(\d{4})/i', $html, $m)) {
                $firstPrize = $m[1];
            }
            if (preg_match('/2nd\s*(?:Prize)?[^0-9]*(\d{4})/i', $html, $m)) {
                $secondPrize = $m[1];
            }
            if (preg_match('/3rd\s*(?:Prize)?[^0-9]*(\d{4})/i', $html, $m)) {
                $thirdPrize = $m[1];
            }
        }

        // GD Lotto specific patterns
        if (! $firstPrize) {
            if (preg_match_all('/(?:Grand Dragon|GD\s*Lotto|GD\s*4D)[^0-9]*(\d{4})\s*(?:\d{4})\s*(?:\d{4})/i', $html, $m)) {
                $firstPrize = $m[1][0] ?? null;
            }
        }

        // Last resort: find sequences of 4-digit numbers (prize table pattern)
        if (! $firstPrize) {
            if (preg_match_all('/\b(\d{4})\b/', $html, $allNumbers)) {
                $numbers = $allNumbers[1];
                // ใช้ 3 เลขแรกที่พบ (มักเป็น 1st, 2nd, 3rd prize)
                if (count($numbers) >= 3) {
                    $firstPrize = $numbers[0];
                    $secondPrize = $numbers[1];
                    $thirdPrize = $numbers[2];
                }
            }
        }

        // Extract draw date
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $html, $dm)) {
            $y = (int) $dm[3];
            if ($y < 100) {
                $y += 2000;
            }
            $drawDate = sprintf('%04d-%02d-%02d', $y, (int) $dm[2], (int) $dm[1]);
        }

        if (! $firstPrize || strlen($firstPrize) !== 4) {
            return ScraperResult::noData($url);
        }

        // Map to standard results
        // สำหรับหวยมาเลย์ในไทย: ใช้ 3 หลักท้ายและ 2 หลักท้ายของรางวัลที่ 1
        $results = [
            'four_top' => $firstPrize,
            'three_top' => substr($firstPrize, -3),
            'two_top' => substr($firstPrize, -2),
        ];

        if ($thirdPrize && strlen($thirdPrize) === 4) {
            $results['four_bottom'] = $thirdPrize;
            $results['two_bottom'] = substr($thirdPrize, -2);
            $results['three_bottom'] = substr($thirdPrize, -3);
        } elseif ($secondPrize && strlen($secondPrize) === 4) {
            $results['two_bottom'] = substr($secondPrize, -2);
        }

        if ($secondPrize) {
            $results['second_prize'] = $secondPrize;
        }
        if ($thirdPrize) {
            $results['third_prize'] = $thirdPrize;
        }

        return ScraperResult::success(
            results: $results,
            rawData: ['first' => $firstPrize, 'second' => $secondPrize, 'third' => $thirdPrize],
            drawDate: $drawDate ?? $date ?? date('Y-m-d'),
            sourceUrl: $url,
            responseTimeMs: $elapsed,
        );
    }
}
