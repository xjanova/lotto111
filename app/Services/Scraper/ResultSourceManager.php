<?php

namespace App\Services\Scraper;

use App\Jobs\ProcessLotteryResult;
use App\Models\AdminLog;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use App\Models\ResultFetchLog;
use App\Models\ResultSource;
use App\Services\Scraper\Providers\HanoiLotteryScraper;
use App\Services\Scraper\Providers\LaoLotteryScraper;
use App\Services\Scraper\Providers\MalaysiaLotteryScraper;
use App\Services\Scraper\Providers\StockLotteryScraper;
use App\Services\Scraper\Providers\ThaiGovernmentScraper;
use App\Services\Scraper\Providers\YeekeeEngine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ตัวจัดการระบบดึงผลหวย
 *
 * จัดการ:
 *   1. Auto mode: ดึงผลอัตโนมัติจากเว็บภายนอก
 *   2. Manual mode: แอดมินกรอกผลเอง
 *   3. สลับ mode ระหว่าง auto/manual
 *   4. Health check & monitoring
 *   5. Scheduling & round matching
 */
class ResultSourceManager
{
    /** @var array<string, ScraperInterface> */
    private array $scrapers = [];

    public function __construct()
    {
        $this->registerScrapers();
    }

    /**
     * ลงทะเบียน scraper providers ทั้งหมด
     */
    private function registerScrapers(): void
    {
        $providers = [
            'thai_government' => new ThaiGovernmentScraper(),
            'lao_lottery' => new LaoLotteryScraper(),
            'hanoi_lottery' => new HanoiLotteryScraper(),
            'malaysia_lottery' => new MalaysiaLotteryScraper(),
            'stock_lottery' => new StockLotteryScraper(),
            'yeekee_internal' => new YeekeeEngine(),
        ];

        foreach ($providers as $name => $scraper) {
            $this->scrapers[$name] = $scraper;
        }
    }

    /**
     * ดึงผลหวยอัตโนมัติสำหรับทุก source ที่ active
     */
    public function fetchAllActive(?string $date = null): array
    {
        $results = [];
        $sources = ResultSource::active()->autoMode()->orderBy('priority', 'desc')->get();

        foreach ($sources as $source) {
            $result = $this->fetchFromSource($source, $date);
            $results[$source->id] = [
                'source' => $source->name,
                'provider' => $source->provider,
                'lottery_type' => $source->lotteryType?->name,
                'success' => $result->success,
                'error' => $result->error,
                'results' => $result->results,
            ];
        }

        return $results;
    }

    /**
     * ดึงผลจาก source เฉพาะ
     */
    public function fetchFromSource(ResultSource $source, ?string $date = null): ScraperResult
    {
        $scraper = $this->getScraper($source->provider);
        if (! $scraper) {
            $error = "Unknown provider: {$source->provider}";
            Log::error("ResultSourceManager: {$error}");

            return ScraperResult::failed($error);
        }

        Log::info("ResultSourceManager: Fetching from [{$source->name}] (provider: {$source->provider})");

        // ใช้ fetchWithRetry ถ้า scraper เป็น AbstractScraper
        if ($scraper instanceof AbstractScraper) {
            $result = $scraper->fetchWithRetry($source, $date);
        } else {
            $result = $scraper->fetch($source, $date);
        }

        // ถ้าสำเร็จ → ลอง auto-submit ผลเข้า round
        if ($result->success) {
            $this->autoSubmitResult($source, $result);
        }

        return $result;
    }

    /**
     * ดึงผลสำหรับ lottery type เฉพาะ
     */
    public function fetchByLotteryType(int $lotteryTypeId, ?string $date = null): ScraperResult
    {
        $source = ResultSource::where('lottery_type_id', $lotteryTypeId)
            ->active()
            ->autoMode()
            ->orderBy('priority', 'desc')
            ->first();

        if (! $source) {
            return ScraperResult::failed("No active auto source for lottery_type_id: {$lotteryTypeId}");
        }

        return $this->fetchFromSource($source, $date);
    }

    /**
     * คำนวณผล Yeekee
     */
    public function calculateYeekeeResult(LotteryRound $round): ScraperResult
    {
        $engine = $this->getYeekeeEngine();

        return $engine->calculateResult($round);
    }

    /**
     * สมัครผลอัตโนมัติเข้ารอบที่เปิดอยู่
     */
    private function autoSubmitResult(ResultSource $source, ScraperResult $result): void
    {
        if (! config('lottery.auto_submit_scraped_results', true)) {
            return;
        }

        // หารอบที่ปิดรับแล้วแต่ยังไม่ออกผล
        $round = LotteryRound::where('lottery_type_id', $source->lottery_type_id)
            ->where('status', 'closed')
            ->whereNull('result_at')
            ->orderBy('close_at', 'desc')
            ->first();

        if (! $round) {
            Log::info("ResultSourceManager: No closed round found for auto-submit (type: {$source->lottery_type_id})");

            return;
        }

        Log::info("ResultSourceManager: Auto-submitting result to round {$round->round_code}");

        // ส่งไป process แบบ async
        ProcessLotteryResult::dispatch($round, $result->results);

        // Update fetch log with round
        ResultFetchLog::where('result_source_id', $source->id)
            ->whereNull('lottery_round_id')
            ->latest('fetched_at')
            ->first()
            ?->update(['lottery_round_id' => $round->id]);
    }

    /**
     * สลับ mode ของ source (auto ↔ manual)
     */
    public function switchMode(ResultSource $source, string $mode, ?int $adminId = null): ResultSource
    {
        $oldMode = $source->mode;
        $source->update(['mode' => $mode]);

        if ($adminId) {
            AdminLog::log(
                $adminId,
                'switch_result_mode',
                "เปลี่ยนโหมดผลหวย [{$source->name}]: {$oldMode} → {$mode}",
                'result_source',
                $source->id,
            );
        }

        Log::info("ResultSourceManager: Mode switched for [{$source->name}]: {$oldMode} → {$mode}");

        return $source->fresh();
    }

    /**
     * Health check ทุก source
     */
    public function healthCheckAll(): array
    {
        $results = [];
        $sources = ResultSource::active()->get();

        foreach ($sources as $source) {
            $scraper = $this->getScraper($source->provider);
            $healthy = $scraper ? $scraper->healthCheck($source) : false;

            $results[$source->id] = [
                'source' => $source->name,
                'provider' => $source->provider,
                'mode' => $source->mode,
                'healthy' => $healthy,
                'last_fetched' => $source->last_fetched_at?->toDateTimeString(),
                'last_status' => $source->last_status,
            ];
        }

        return $results;
    }

    /**
     * Health check source เฉพาะ
     */
    public function healthCheck(ResultSource $source): bool
    {
        $scraper = $this->getScraper($source->provider);

        return $scraper ? $scraper->healthCheck($source) : false;
    }

    /**
     * ดึง scraper instance
     */
    public function getScraper(string $provider): ?ScraperInterface
    {
        return $this->scrapers[$provider] ?? null;
    }

    /**
     * ดึง YeekeeEngine instance
     */
    public function getYeekeeEngine(): YeekeeEngine
    {
        return $this->scrapers['yeekee_internal'];
    }

    /**
     * ดู provider ทั้งหมดที่ลงทะเบียน
     */
    public function getRegisteredProviders(): array
    {
        $providers = [];
        foreach ($this->scrapers as $name => $scraper) {
            $providers[$name] = [
                'name' => $scraper->getProviderName(),
                'supported_slugs' => $scraper->getSupportedSlugs(),
            ];
        }

        return $providers;
    }

    /**
     * สรุปสถานะระบบ
     */
    public function getSystemStatus(): array
    {
        $sources = ResultSource::with('lotteryType')->get();
        $recentLogs = ResultFetchLog::with('resultSource')
            ->latest('fetched_at')
            ->limit(20)
            ->get();

        $stats = [
            'total_sources' => $sources->count(),
            'active_auto' => $sources->where('is_active', true)->where('mode', 'auto')->count(),
            'active_manual' => $sources->where('is_active', true)->where('mode', 'manual')->count(),
            'inactive' => $sources->where('is_active', false)->count(),
            'last_24h_fetches' => ResultFetchLog::where('fetched_at', '>=', now()->subDay())->count(),
            'last_24h_success' => ResultFetchLog::where('fetched_at', '>=', now()->subDay())->where('status', 'success')->count(),
            'last_24h_failed' => ResultFetchLog::where('fetched_at', '>=', now()->subDay())->whereIn('status', ['failed', 'timeout', 'parse_error'])->count(),
        ];

        return [
            'stats' => $stats,
            'sources' => $sources,
            'recent_logs' => $recentLogs,
            'registered_providers' => $this->getRegisteredProviders(),
        ];
    }

    /**
     * ดูประวัติ fetch ของ source
     */
    public function getSourceHistory(int $sourceId, int $limit = 50): mixed
    {
        return ResultFetchLog::where('result_source_id', $sourceId)
            ->with('round')
            ->latest('fetched_at')
            ->limit($limit)
            ->get();
    }

    /**
     * ล้าง fetch logs เก่ากว่า N วัน
     */
    public function cleanOldLogs(int $days = 30): int
    {
        return ResultFetchLog::where('fetched_at', '<', now()->subDays($days))->delete();
    }

    /**
     * ทดสอบ scrape (dry run - ไม่ submit ผลจริง)
     */
    public function testScrape(ResultSource $source, ?string $date = null): ScraperResult
    {
        $scraper = $this->getScraper($source->provider);
        if (! $scraper) {
            return ScraperResult::failed("Unknown provider: {$source->provider}");
        }

        // fetch โดยไม่ auto-submit
        return $scraper->fetch($source, $date);
    }
}
