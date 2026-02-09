<?php

namespace App\Console\Commands;

use App\Models\ResultSource;
use App\Services\Scraper\ResultSourceManager;
use Illuminate\Console\Command;

/**
 * ดึงผลหวยอัตโนมัติจากทุกแหล่งที่ active
 *
 * Usage:
 *   php artisan lottery:fetch-results                 # ดึงทั้งหมด
 *   php artisan lottery:fetch-results --source=1      # ดึงจาก source ID 1
 *   php artisan lottery:fetch-results --provider=thai_government  # ดึงตาม provider
 *   php artisan lottery:fetch-results --date=2024-01-16           # ดึงผลวันที่ระบุ
 *   php artisan lottery:fetch-results --dry-run       # ทดสอบโดยไม่ submit ผล
 */
class FetchLotteryResults extends Command
{
    protected $signature = 'lottery:fetch-results
        {--source= : Source ID เฉพาะ}
        {--provider= : Provider name (thai_government, lao_lottery, etc.)}
        {--date= : วันที่ต้องการดึง (Y-m-d)}
        {--dry-run : ทดสอบโดยไม่ submit ผลจริง}
        {--force : ดึงแม้ว่า source จะเป็น manual mode}';

    protected $description = 'ดึงผลหวยอัตโนมัติจากแหล่งข้อมูลภายนอก';

    public function handle(ResultSourceManager $manager): int
    {
        $date = $this->option('date');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - จะไม่ submit ผลจริง');
        }

        // ดึงจาก source เฉพาะ
        if ($sourceId = $this->option('source')) {
            return $this->fetchFromSource($manager, (int) $sourceId, $date, $isDryRun);
        }

        // ดึงตาม provider
        if ($provider = $this->option('provider')) {
            return $this->fetchByProvider($manager, $provider, $date, $isDryRun);
        }

        // ดึงทั้งหมด
        return $this->fetchAll($manager, $date, $isDryRun);
    }

    private function fetchFromSource(ResultSourceManager $manager, int $sourceId, ?string $date, bool $isDryRun): int
    {
        $source = ResultSource::with('lotteryType')->find($sourceId);
        if (! $source) {
            $this->error("ไม่พบ source ID: {$sourceId}");

            return self::FAILURE;
        }

        $this->info("กำลังดึงจาก: {$source->name} ({$source->provider})");

        $result = $isDryRun
            ? $manager->testScrape($source, $date)
            : $manager->fetchFromSource($source, $date);

        $this->displayResult($source->name, $result);

        return $result->success ? self::SUCCESS : self::FAILURE;
    }

    private function fetchByProvider(ResultSourceManager $manager, string $provider, ?string $date, bool $isDryRun): int
    {
        $query = ResultSource::with('lotteryType')
            ->where('provider', $provider)
            ->where('is_active', true);

        if (! $this->option('force')) {
            $query->where('mode', 'auto');
        }

        $sources = $query->orderBy('priority', 'desc')->get();

        if ($sources->isEmpty()) {
            $this->warn("ไม่พบ active source สำหรับ provider: {$provider}");

            return self::FAILURE;
        }

        $success = 0;
        $failed = 0;

        foreach ($sources as $source) {
            $this->info("กำลังดึงจาก: {$source->name}");

            $result = $isDryRun
                ? $manager->testScrape($source, $date)
                : $manager->fetchFromSource($source, $date);

            $this->displayResult($source->name, $result);

            if ($result->success) {
                $success++;
            } else {
                $failed++;
            }
        }

        $this->newLine();
        $this->info("สรุป: สำเร็จ {$success}, ล้มเหลว {$failed}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function fetchAll(ResultSourceManager $manager, ?string $date, bool $isDryRun): int
    {
        $this->info('กำลังดึงผลหวยจากทุกแหล่ง...');
        $this->newLine();

        if ($isDryRun) {
            $sources = ResultSource::with('lotteryType')->active()->autoMode()->orderBy('priority', 'desc')->get();
            $success = 0;
            $failed = 0;

            foreach ($sources as $source) {
                $this->info("  [{$source->provider}] {$source->name}");
                $result = $manager->testScrape($source, $date);
                $this->displayResult($source->name, $result);

                if ($result->success) {
                    $success++;
                } else {
                    $failed++;
                }
            }

            $this->newLine();
            $this->info("DRY RUN สรุป: สำเร็จ {$success}, ล้มเหลว {$failed}");

            return self::SUCCESS;
        }

        $results = $manager->fetchAllActive($date);

        $table = [];
        $success = 0;
        $failed = 0;

        foreach ($results as $id => $data) {
            $status = $data['success'] ? '<fg=green>OK</>' : '<fg=red>FAIL</>';
            $resultStr = $data['success']
                ? implode(', ', array_map(fn ($k, $v) => "{$k}={$v}", array_keys($data['results']), $data['results']))
                : ($data['error'] ?? 'Unknown error');

            $table[] = [
                $data['source'],
                $data['provider'],
                $data['lottery_type'] ?? '-',
                $status,
                mb_substr($resultStr, 0, 60),
            ];

            if ($data['success']) {
                $success++;
            } else {
                $failed++;
            }
        }

        $this->table(['Source', 'Provider', 'Lottery Type', 'Status', 'Results/Error'], $table);
        $this->newLine();
        $this->info("สรุป: สำเร็จ {$success}, ล้มเหลว {$failed}, รวม " . count($results));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function displayResult(string $name, \App\Services\Scraper\ScraperResult $result): void
    {
        if ($result->success) {
            $this->info("  ✓ {$name}: สำเร็จ ({$result->responseTimeMs}ms)");
            foreach ($result->results as $type => $value) {
                if (! is_array($value)) {
                    $this->line("    {$type} = {$value}");
                }
            }
        } else {
            $this->error("  ✗ {$name}: {$result->error}");
        }
    }
}
