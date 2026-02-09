<?php

namespace App\Services\Scraper\Providers;

use App\Models\ResultSource;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\ScraperResult;
use Illuminate\Support\Facades\Log;

/**
 * หวยรัฐบาลไทย (สลากกินแบ่งรัฐบาล)
 *
 * Primary:  GLO Official API (glo.or.th) - JSON response
 * Fallback: Thai Lotto API (lotto.api.rayriffy.com) - Community API
 *
 * ออกรางวัล: วันที่ 1 และ 16 ของทุกเดือน เวลา ~15:00-16:00
 *
 * Result mapping:
 *   - first_prize    → เลขรางวัลที่ 1 (6 หลัก) → ตัด 3 หลักท้าย = three_top, 2 หลักท้าย = two_top
 *   - front_three    → เลขหน้า 3 ตัว (2 ชุด)
 *   - back_three     → เลขท้าย 3 ตัว (2 ชุด) → three_bottom
 *   - last_two       → เลขท้าย 2 ตัว (1 ชุด) → two_bottom
 */
class ThaiGovernmentScraper extends AbstractScraper
{
    public function getProviderName(): string
    {
        return 'thai_government';
    }

    public function getSupportedSlugs(): array
    {
        return ['government-thai', 'government-set'];
    }

    public function fetch(ResultSource $source, ?string $date = null): ScraperResult
    {
        // Try primary source (GLO API)
        $result = $this->fetchFromGlo($source, $date);
        if ($result->success) {
            return $result;
        }

        // Try fallback (rayriffy API)
        Log::info("ThaiGov: GLO API failed, trying fallback rayriffy API");

        return $this->fetchFromRayriffy($source, $date);
    }

    /**
     * ดึงจาก GLO Official API
     */
    private function fetchFromGlo(ResultSource $source, ?string $date = null): ScraperResult
    {
        $url = $source->source_url ?: 'https://www.glo.or.th/api/lottery/getLatestLottery';

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($url, $source) {
                return $this->httpPost($url, [], $source->timeout_seconds, [
                    'Content-Type' => 'application/json',
                    'Referer' => 'https://www.glo.or.th/',
                ]);
            });

            if (! $response->successful()) {
                return ScraperResult::failed(
                    "GLO API returned HTTP {$response->status()}",
                    $url,
                    $elapsed,
                );
            }

            $data = $response->json();
            if (empty($data)) {
                return ScraperResult::failed('GLO API returned empty response', $url, $elapsed);
            }

            return $this->parseGloResponse($data, $url, $elapsed);
        } catch (\Throwable $e) {
            return ScraperResult::failed("GLO API error: {$e->getMessage()}", $url);
        }
    }

    /**
     * Parse response จาก GLO API
     */
    private function parseGloResponse(array $data, string $url, int $elapsed): ScraperResult
    {
        try {
            // GLO API returns nested structure with prize data
            $response = $data['response'] ?? $data;

            // Find first prize
            $firstPrize = null;
            $frontThree = [];
            $backThree = [];
            $lastTwo = null;

            // Handle different GLO API response formats
            $prizes = $response['data'] ?? $response['prizes'] ?? $response;

            if (is_array($prizes)) {
                foreach ($prizes as $prize) {
                    $id = $prize['id'] ?? $prize['prize_type'] ?? '';
                    $numbers = $prize['number'] ?? $prize['numbers'] ?? [];

                    if (is_string($numbers)) {
                        $numbers = [$numbers];
                    }

                    switch (true) {
                        case str_contains(strtolower($id), 'first') || $id === 'prizeFirst':
                            $firstPrize = $numbers[0] ?? null;
                            break;
                        case str_contains(strtolower($id), 'frontthree') || $id === 'runningNumberFrontThree':
                            $frontThree = $numbers;
                            break;
                        case str_contains(strtolower($id), 'backthree') || $id === 'runningNumberBackThree':
                            $backThree = $numbers;
                            break;
                        case str_contains(strtolower($id), 'backtwo') || $id === 'runningNumberBackTwo':
                            $lastTwo = $numbers[0] ?? null;
                            break;
                    }
                }
            }

            if (! $firstPrize) {
                return ScraperResult::noData($url);
            }

            $results = $this->mapToStandardResults($firstPrize, $frontThree, $backThree, $lastTwo);

            return ScraperResult::success(
                results: $results,
                rawData: $data,
                drawDate: $response['date'] ?? date('Y-m-d'),
                sourceUrl: $url,
                responseTimeMs: $elapsed,
            );
        } catch (\Throwable $e) {
            return ScraperResult::failed("Parse error: {$e->getMessage()}", $url, $elapsed);
        }
    }

    /**
     * ดึงจาก rayriffy Thai Lotto API (Community maintained)
     */
    private function fetchFromRayriffy(ResultSource $source, ?string $date = null): ScraperResult
    {
        $url = $source->fallback_url ?: 'https://lotto.api.rayriffy.com/latest';

        try {
            [$response, $elapsed] = $this->measureTime(function () use ($url, $source) {
                return $this->httpGet($url, $source->timeout_seconds);
            });

            if (! $response->successful()) {
                return ScraperResult::failed(
                    "Rayriffy API returned HTTP {$response->status()}",
                    $url,
                    $elapsed,
                );
            }

            $data = $response->json();
            if (($data['status'] ?? '') !== 'success') {
                return ScraperResult::failed('Rayriffy API returned non-success status', $url, $elapsed);
            }

            $resp = $data['response'] ?? [];
            $firstPrize = null;
            $frontThree = [];
            $backThree = [];
            $lastTwo = null;

            // Parse prizes
            foreach ($resp['prizes'] ?? [] as $prize) {
                if (($prize['id'] ?? '') === 'prizeFirst') {
                    $firstPrize = $prize['number'][0] ?? null;
                }
            }

            // Parse running numbers
            foreach ($resp['runningNumbers'] ?? [] as $running) {
                switch ($running['id'] ?? '') {
                    case 'runningNumberFrontThree':
                        $frontThree = $running['number'] ?? [];
                        break;
                    case 'runningNumberBackThree':
                        $backThree = $running['number'] ?? [];
                        break;
                    case 'runningNumberBackTwo':
                        $lastTwo = ($running['number'] ?? [])[0] ?? null;
                        break;
                }
            }

            if (! $firstPrize) {
                return ScraperResult::noData($url);
            }

            $results = $this->mapToStandardResults($firstPrize, $frontThree, $backThree, $lastTwo);

            return ScraperResult::success(
                results: $results,
                rawData: $data,
                drawDate: $resp['date'] ?? date('Y-m-d'),
                sourceUrl: $url,
                responseTimeMs: $elapsed,
            );
        } catch (\Throwable $e) {
            return ScraperResult::failed("Rayriffy API error: {$e->getMessage()}", $url);
        }
    }

    /**
     * Map ผลหวยรัฐบาลเป็น standard result format
     *
     * สำหรับหวยใต้ดิน/ออนไลน์:
     * - three_top = 3 หลักท้ายของรางวัลที่ 1
     * - two_top = 2 หลักท้ายของรางวัลที่ 1
     * - two_bottom = เลขท้าย 2 ตัว
     * - three_bottom = เลขท้าย 3 ตัว (ตัวแรก)
     */
    private function mapToStandardResults(
        string $firstPrize,
        array $frontThree,
        array $backThree,
        ?string $lastTwo,
    ): array {
        $results = [
            'first_prize' => $firstPrize,
            'three_top' => substr($firstPrize, -3),
            'two_top' => substr($firstPrize, -2),
        ];

        if ($lastTwo) {
            $results['two_bottom'] = $lastTwo;
        }

        if (! empty($backThree)) {
            // เลขท้าย 3 ตัว - ใช้ตัวแรกเป็น three_bottom
            $results['three_bottom'] = $backThree[0] ?? '';
            $results['back_three_set'] = $backThree;
        }

        if (! empty($frontThree)) {
            $results['front_three_set'] = $frontThree;
        }

        return $results;
    }
}
