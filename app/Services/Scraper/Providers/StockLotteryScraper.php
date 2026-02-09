<?php

namespace App\Services\Scraper\Providers;

use App\Models\ResultSource;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\ScraperResult;
use Illuminate\Support\Facades\Log;

/**
 * หวยหุ้น (Stock Index Lottery)
 *
 * คำนวณจากดัชนีหุ้นจริง หรือดึงผลจากเว็บ lottosociety.com
 *
 * หวยหุ้นไทย (SET Index):
 *   - 3 ตัวบน = คำนวณจาก SET Index + SET50 decimal digits
 *   - 2 ตัวล่าง = คำนวณจากค่าเปลี่ยนแปลง decimal digits
 *   - 4 รอบ/วัน: 10:00, 12:30, 14:30, 16:45 (จันทร์-ศุกร์)
 *
 * หวยหุ้นต่างประเทศ (Nikkei, Hang Seng, Dow Jones, etc.):
 *   - ดึงจาก lottosociety.com หรือเว็บที่ publish ผลหวยหุ้น
 *
 * Supported slugs: nikkei, hang-seng, dowjones, china, korea, taiwan, singapore, uk, germany, russia
 */
class StockLotteryScraper extends AbstractScraper
{
    /** Mapping slug → keyword สำหรับค้นหาในเว็บ */
    private const SLUG_KEYWORDS = [
        'nikkei' => ['นิเคอิ', 'Nikkei', 'นิเคอิ'],
        'hang-seng' => ['ฮั่งเส็ง', 'Hang Seng', 'HSI'],
        'dowjones' => ['ดาวโจนส์', 'Dow Jones', 'DJIA'],
        'china' => ['จีน', 'China', 'SSE', 'Shanghai'],
        'korea' => ['เกาหลี', 'Korea', 'KOSPI'],
        'taiwan' => ['ไต้หวัน', 'Taiwan', 'TWSE', 'TAIEX'],
        'singapore' => ['สิงคโปร์', 'Singapore', 'STI'],
        'uk' => ['อังกฤษ', 'UK', 'FTSE'],
        'germany' => ['เยอรมัน', 'Germany', 'DAX'],
        'russia' => ['รัสเซีย', 'Russia', 'MOEX'],
    ];

    public function getProviderName(): string
    {
        return 'stock_lottery';
    }

    public function getSupportedSlugs(): array
    {
        return array_keys(self::SLUG_KEYWORDS);
    }

    public function fetch(ResultSource $source, ?string $date = null): ScraperResult
    {
        $slug = $source->lotteryType?->slug ?? '';
        $url = $source->source_url ?: 'https://www.lottosociety.com/';

        // ลองดึงจาก settrade.com ถ้าเป็นหุ้นไทย
        if (in_array($slug, ['set-thai', 'stock-thai'])) {
            return $this->fetchThaiStock($source, $date);
        }

        // ดึงจาก lottosociety.com หรือ source ที่ตั้งไว้
        return $this->fetchFromLottoSociety($source, $slug, $date);
    }

    /**
     * ดึงผลหวยหุ้นจาก lottosociety.com
     */
    private function fetchFromLottoSociety(ResultSource $source, string $slug, ?string $date = null): ScraperResult
    {
        $url = $source->source_url ?: 'https://www.lottosociety.com/';

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($url, $source) {
                return $this->httpGet($url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return $this->tryFallback($source, $slug, $date, "HTTP {$response->status()}");
            }

            $html = $response->body();
            $keywords = self::SLUG_KEYWORDS[$slug] ?? [$slug];

            return $this->parseStockResult($html, $url, $elapsed, $keywords, $date);
        } catch (\Throwable $e) {
            return $this->tryFallback($source, $slug, $date, $e->getMessage());
        }
    }

    /**
     * ดึงผลหวยหุ้นไทยจาก SET Index (settrade.com)
     */
    private function fetchThaiStock(ResultSource $source, ?string $date = null): ScraperResult
    {
        $url = $source->source_url ?: 'https://www.settrade.com/th/equities/market-summary/overview';

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($url, $source) {
                return $this->httpGet($url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return ScraperResult::failed("Settrade HTTP {$response->status()}", $url, $elapsed);
            }

            $html = $response->body();

            // Extract SET Index value
            $setIndex = null;
            $setChange = null;

            // Try JSON embedded data
            if (preg_match('/"last"\s*:\s*([\d,.]+)/', $html, $m)) {
                $setIndex = str_replace(',', '', $m[1]);
            }
            if (preg_match('/"change"\s*:\s*([+-]?[\d,.]+)/', $html, $m)) {
                $setChange = str_replace(',', '', $m[1]);
            }

            // Fallback to HTML parsing
            if (! $setIndex) {
                if (preg_match('/SET\s*Index[^0-9]*([\d,]+\.\d{2})/i', $html, $m)) {
                    $setIndex = str_replace(',', '', $m[1]);
                }
            }

            if (! $setIndex) {
                return ScraperResult::noData($url);
            }

            // Calculate lottery numbers from SET Index
            $results = $this->calculateStockLotteryNumbers((float) $setIndex, $setChange ? (float) $setChange : null);

            return ScraperResult::success(
                results: $results,
                rawData: ['set_index' => $setIndex, 'change' => $setChange],
                drawDate: $date ?? date('Y-m-d'),
                sourceUrl: $url,
                responseTimeMs: $elapsed,
            );
        } catch (\Throwable $e) {
            return ScraperResult::failed("Settrade error: {$e->getMessage()}", $url);
        }
    }

    /**
     * คำนวณเลขหวยหุ้นจากดัชนี
     *
     * วิธีคำนวณ (แบบมาตรฐาน):
     * 3 ตัวบน = ทศนิยม 2 ตำแหน่งของดัชนี + หลักหน่วยดัชนี
     * 2 ตัวล่าง = ทศนิยม 2 ตำแหน่งของค่าเปลี่ยนแปลง
     */
    private function calculateStockLotteryNumbers(float $index, ?float $change): array
    {
        // 3 ตัวบน: ใช้ทศนิยมของดัชนี
        $decimal = explode('.', number_format($index, 2, '.', ''));
        $decimalPart = $decimal[1] ?? '00';
        $unitDigit = substr($decimal[0], -1);
        $threeTop = $decimalPart . $unitDigit;

        // 2 ตัวล่าง: ใช้ทศนิยมของค่าเปลี่ยนแปลง
        $twoBottom = '00';
        if ($change !== null) {
            $absChange = abs($change);
            $changeParts = explode('.', number_format($absChange, 2, '.', ''));
            $twoBottom = $changeParts[1] ?? '00';
        }

        return [
            'three_top' => str_pad($threeTop, 3, '0', STR_PAD_LEFT),
            'two_bottom' => str_pad($twoBottom, 2, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * Parse stock lottery results from HTML
     */
    private function parseStockResult(string $html, string $url, int $elapsed, array $keywords, ?string $date = null): ScraperResult
    {
        $threeTop = null;
        $twoBottom = null;

        // ค้นหา keyword ในเนื้อหา
        foreach ($keywords as $keyword) {
            // Pattern: keyword followed by 3-digit and 2-digit numbers
            $escaped = preg_quote($keyword, '/');

            // Pattern 1: "keyword ... 3 ตัวบน XXX ... 2 ตัวล่าง XX"
            if (preg_match("/{$escaped}[^\\d]*(?:3\\s*ตัว(?:บน)?|บน)[^\\d]*(\\d{3})/u", $html, $m)) {
                $threeTop = $m[1];
            }
            if (preg_match("/{$escaped}[^\\d]*(?:2\\s*ตัว(?:ล่าง)?|ล่าง)[^\\d]*(\\d{2})/u", $html, $m)) {
                $twoBottom = $m[1];
            }

            // Pattern 2: Table/structured format "XXX | XX"
            if (! $threeTop && preg_match("/{$escaped}[^\\d]*(\\d{3})[^\\d]+(\\d{2})/u", $html, $m)) {
                $threeTop = $m[1];
                $twoBottom = $m[2];
            }

            if ($threeTop) {
                break;
            }
        }

        if (! $threeTop) {
            return ScraperResult::noData($url);
        }

        $results = [
            'three_top' => $threeTop,
        ];

        if ($twoBottom) {
            $results['two_bottom'] = $twoBottom;
        }

        return ScraperResult::success(
            results: $results,
            rawData: ['keywords_used' => $keywords, 'html_length' => strlen($html)],
            drawDate: $date ?? date('Y-m-d'),
            sourceUrl: $url,
            responseTimeMs: $elapsed,
        );
    }

    /**
     * ลอง fallback URL
     */
    private function tryFallback(ResultSource $source, string $slug, ?string $date, string $error): ScraperResult
    {
        if (empty($source->fallback_url)) {
            return ScraperResult::failed("Primary failed: {$error}, no fallback");
        }

        Log::info("StockLottery [{$slug}]: Primary failed ({$error}), trying fallback");

        try {
            $keywords = self::SLUG_KEYWORDS[$slug] ?? [$slug];
            [$response, $elapsed] = $this->measureTime(function () use ($source) {
                return $this->httpGet($source->fallback_url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return ScraperResult::failed("Both sources failed");
            }

            return $this->parseStockResult($response->body(), $source->fallback_url, $elapsed, $keywords, $date);
        } catch (\Throwable $e) {
            return ScraperResult::failed("Both sources failed. Fallback: {$e->getMessage()}");
        }
    }
}
