<?php

namespace App\Services\Scraper\Providers;

use App\Models\ResultSource;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\ScraperResult;
use Illuminate\Support\Facades\Log;

/**
 * หวยฮานอย (Hanoi Lottery / เวียดนาม)
 *
 * มี 4 รอบต่อวัน:
 *   - ฮานอยเฉพาะกิจ (Special Mission) → 16:30
 *   - ฮานอยพิเศษ (Special) → 17:30
 *   - ฮานอยปกติ (Regular) → 18:30
 *   - ฮานอย VIP → 19:30
 *
 * ออกทุกวัน (รวมเสาร์-อาทิตย์)
 *
 * ผล: เลข 5 หลัก (รางวัลพิเศษ) + เลข 5 หลัก (รางวัลที่ 1)
 *   - three_top = 3 หลักท้ายของรางวัลพิเศษ
 *   - two_top = 2 หลักท้ายของรางวัลพิเศษ
 *   - two_bottom = 2 หลักท้ายของรางวัลที่ 1
 *   - three_bottom = 3 หลักท้ายของรางวัลที่ 1
 */
class HanoiLotteryScraper extends AbstractScraper
{
    /** Mapping slug → variant keyword */
    private const VARIANT_MAP = [
        'hanoi' => 'ปกติ',
        'hanoi-vip' => 'VIP',
    ];

    public function getProviderName(): string
    {
        return 'hanoi_lottery';
    }

    public function getSupportedSlugs(): array
    {
        return ['hanoi', 'hanoi-vip', 'hanoi-set'];
    }

    public function fetch(ResultSource $source, ?string $date = null): ScraperResult
    {
        $url = $source->source_url ?: 'https://lotto.mthai.com/lottery/hanoi';
        $variant = $source->getConfig('variant', 'ปกติ');

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($url, $source) {
                return $this->httpGet($url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return $this->tryFallback($source, $date, $variant, "HTTP {$response->status()}");
            }

            $html = $response->body();
            $result = $this->parseHanoiResult($html, $url, $elapsed, $variant, $date);

            if ($result->success) {
                return $result;
            }

            return $this->tryFallback($source, $date, $variant, $result->error ?? 'Parse failed');
        } catch (\Throwable $e) {
            return $this->tryFallback($source, $date, $variant, $e->getMessage());
        }
    }

    /**
     * Parse HTML เพื่อหาผลฮานอย
     */
    private function parseHanoiResult(string $html, string $url, int $elapsed, string $variant, ?string $date = null): ScraperResult
    {
        $specialPrize = null;
        $firstPrize = null;
        $drawDate = null;

        $dom = $this->parseHtml($html);

        // Try structured selectors first
        $selectors = [
            "//div[contains(@class, 'lottery-result')]//div[contains(@class, 'prize')]",
            "//table[contains(@class, 'result')]//tr",
            "//div[contains(@class, 'result-hanoi')]",
        ];

        foreach ($selectors as $selector) {
            $nodes = $this->xpath($dom, $selector);
            for ($i = 0; $i < $nodes->length; $i++) {
                $text = $nodes->item($i)->textContent;
                // Match special prize
                if (preg_match('/(?:รางวัลพิเศษ|Đặc biệt|พิเศษ)[^\d]*(\d{5})/u', $text, $m)) {
                    $specialPrize = $m[1];
                }
                // Match first prize
                if (preg_match('/(?:รางวัลที่\s*1|Giải nhất|ที่\s*1)[^\d]*(\d{5})/u', $text, $m)) {
                    $firstPrize = $m[1];
                }
            }
        }

        // Regex fallback scan
        if (! $specialPrize) {
            // Look for variant-specific results
            $variantPattern = preg_quote($variant, '/');
            if (preg_match("/(?:ฮานอย\s*{$variantPattern}|{$variantPattern})[^\\d]*(?:รางวัลพิเศษ|พิเศษ)[^\\d]*(\\d{5})/u", $html, $m)) {
                $specialPrize = $m[1];
            }
        }

        if (! $specialPrize) {
            if (preg_match('/(?:รางวัลพิเศษ|Đặc biệt|Special\s*Prize)[^\d]*(\d{5})/ui', $html, $m)) {
                $specialPrize = $m[1];
            }
        }

        if (! $firstPrize) {
            if (preg_match('/(?:รางวัลที่\s*1|Giải nhất|First\s*Prize)[^\d]*(\d{5})/ui', $html, $m)) {
                $firstPrize = $m[1];
            }
        }

        // Try to get draw date
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $html, $dateMatch)) {
            $y = (int) $dateMatch[3];
            if ($y < 100) {
                $y += 2000;
            }
            if ($y > 2500) {
                $y -= 543;
            }
            $drawDate = sprintf('%04d-%02d-%02d', $y, (int) $dateMatch[2], (int) $dateMatch[1]);
        }

        if (! $specialPrize && ! $firstPrize) {
            return ScraperResult::noData($url);
        }

        // Build results
        $results = [];

        if ($specialPrize) {
            $results['special_prize'] = $specialPrize;
            $results['three_top'] = substr($specialPrize, -3);
            $results['two_top'] = substr($specialPrize, -2);
        }

        if ($firstPrize) {
            $results['first_prize'] = $firstPrize;
            $results['three_bottom'] = substr($firstPrize, -3);
            $results['two_bottom'] = substr($firstPrize, -2);
        }

        // ถ้ามีแค่ special prize → ใช้เป็นทั้ง top และ bottom
        if ($specialPrize && ! $firstPrize) {
            $results['two_bottom'] = substr($specialPrize, -2);
        }

        return ScraperResult::success(
            results: $results,
            rawData: ['special' => $specialPrize, 'first' => $firstPrize, 'variant' => $variant],
            drawDate: $drawDate ?? $date ?? date('Y-m-d'),
            sourceUrl: $url,
            responseTimeMs: $elapsed,
        );
    }

    /**
     * ลอง fallback
     */
    private function tryFallback(ResultSource $source, ?string $date, string $variant, string $error): ScraperResult
    {
        if (empty($source->fallback_url)) {
            return ScraperResult::failed("Primary failed: {$error}, no fallback configured");
        }

        Log::info("HanoiLottery: Primary failed ({$error}), trying fallback");

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($source) {
                return $this->httpGet($source->fallback_url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return ScraperResult::failed("Both sources failed");
            }

            return $this->parseHanoiResult($response->body(), $source->fallback_url, $elapsed, $variant, $date);
        } catch (\Throwable $e) {
            return ScraperResult::failed("Both sources failed. Fallback: {$e->getMessage()}");
        }
    }
}
