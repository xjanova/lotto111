<?php

namespace App\Console\Commands;

use App\Models\ResultSource;
use App\Services\Scraper\ResultSourceManager;
use Illuminate\Console\Command;

/**
 * จัดการ Result Sources
 *
 * Usage:
 *   php artisan lottery:sources list               # แสดง source ทั้งหมด
 *   php artisan lottery:sources status              # ดูสถานะระบบ
 *   php artisan lottery:sources health              # Health check
 *   php artisan lottery:sources switch 1 manual     # สลับ mode
 *   php artisan lottery:sources test 1              # ทดสอบ scrape
 *   php artisan lottery:sources history 1            # ดูประวัติ
 *   php artisan lottery:sources providers            # ดู providers ที่ลงทะเบียน
 *   php artisan lottery:sources cleanup --days=30    # ล้าง logs เก่า
 */
class ManageResultSources extends Command
{
    protected $signature = 'lottery:sources
        {action=list : Action (list, status, health, switch, test, history, providers, cleanup)}
        {id? : Source ID (สำหรับ switch, test, history)}
        {mode? : Mode (auto/manual) สำหรับ switch}
        {--days=30 : จำนวนวันสำหรับ cleanup}';

    protected $description = 'จัดการแหล่งข้อมูลผลหวย';

    public function handle(ResultSourceManager $manager): int
    {
        return match ($this->argument('action')) {
            'list' => $this->listSources(),
            'status' => $this->showStatus($manager),
            'health' => $this->healthCheck($manager),
            'switch' => $this->switchMode($manager),
            'test' => $this->testScrape($manager),
            'history' => $this->showHistory($manager),
            'providers' => $this->showProviders($manager),
            'cleanup' => $this->cleanup($manager),
            default => $this->showHelp(),
        };
    }

    private function listSources(): int
    {
        $sources = ResultSource::with('lotteryType')->orderBy('lottery_type_id')->get();

        $table = [];
        foreach ($sources as $s) {
            $table[] = [
                $s->id,
                $s->name,
                $s->provider,
                $s->lotteryType?->name ?? '-',
                $s->mode,
                $s->is_active ? 'Active' : 'Inactive',
                $s->last_status ?? '-',
                $s->last_fetched_at?->diffForHumans() ?? 'Never',
            ];
        }

        $this->table(['ID', 'Name', 'Provider', 'Lottery Type', 'Mode', 'Status', 'Last Result', 'Last Fetch'], $table);

        return self::SUCCESS;
    }

    private function showStatus(ResultSourceManager $manager): int
    {
        $status = $manager->getSystemStatus();
        $stats = $status['stats'];

        $this->info('=== Result Source System Status ===');
        $this->newLine();
        $this->line("Total Sources:     {$stats['total_sources']}");
        $this->line("Active (Auto):     {$stats['active_auto']}");
        $this->line("Active (Manual):   {$stats['active_manual']}");
        $this->line("Inactive:          {$stats['inactive']}");
        $this->newLine();
        $this->line("Last 24h Fetches:  {$stats['last_24h_fetches']}");
        $this->line("  Successful:      {$stats['last_24h_success']}");
        $this->line("  Failed:          {$stats['last_24h_failed']}");

        return self::SUCCESS;
    }

    private function healthCheck(ResultSourceManager $manager): int
    {
        $this->info('Running health checks...');
        $results = $manager->healthCheckAll();

        $table = [];
        foreach ($results as $id => $data) {
            $table[] = [
                $id,
                $data['source'],
                $data['provider'],
                $data['mode'],
                $data['healthy'] ? '<fg=green>Healthy</>' : '<fg=red>Down</>',
                $data['last_status'] ?? '-',
                $data['last_fetched'] ?? 'Never',
            ];
        }

        $this->table(['ID', 'Source', 'Provider', 'Mode', 'Health', 'Last Status', 'Last Fetch'], $table);

        return self::SUCCESS;
    }

    private function switchMode(ResultSourceManager $manager): int
    {
        $id = $this->argument('id');
        $mode = $this->argument('mode');

        if (! $id || ! $mode) {
            $this->error('Usage: lottery:sources switch {id} {auto|manual}');

            return self::FAILURE;
        }

        if (! in_array($mode, ['auto', 'manual'])) {
            $this->error("Mode ต้องเป็น 'auto' หรือ 'manual'");

            return self::FAILURE;
        }

        $source = ResultSource::find($id);
        if (! $source) {
            $this->error("ไม่พบ source ID: {$id}");

            return self::FAILURE;
        }

        $updated = $manager->switchMode($source, $mode);
        $this->info("เปลี่ยน [{$updated->name}] เป็น mode: {$mode} สำเร็จ");

        return self::SUCCESS;
    }

    private function testScrape(ResultSourceManager $manager): int
    {
        $id = $this->argument('id');
        if (! $id) {
            $this->error('Usage: lottery:sources test {id}');

            return self::FAILURE;
        }

        $source = ResultSource::with('lotteryType')->find($id);
        if (! $source) {
            $this->error("ไม่พบ source ID: {$id}");

            return self::FAILURE;
        }

        $this->info("ทดสอบ scrape: {$source->name} ({$source->provider})");
        $this->info("URL: {$source->source_url}");
        $this->newLine();

        $result = $manager->testScrape($source);

        if ($result->success) {
            $this->info('✓ Scrape สำเร็จ!');
            $this->line("Draw Date: {$result->drawDate}");
            $this->line("Response Time: {$result->responseTimeMs}ms");
            $this->newLine();
            $this->info('Results:');
            foreach ($result->results as $type => $value) {
                if (is_array($value)) {
                    $this->line("  {$type} = " . json_encode($value));
                } else {
                    $this->line("  {$type} = {$value}");
                }
            }
        } else {
            $this->error("✗ Scrape ล้มเหลว: {$result->error}");
        }

        return $result->success ? self::SUCCESS : self::FAILURE;
    }

    private function showHistory(ResultSourceManager $manager): int
    {
        $id = $this->argument('id');
        if (! $id) {
            $this->error('Usage: lottery:sources history {id}');

            return self::FAILURE;
        }

        $logs = $manager->getSourceHistory((int) $id, 20);

        $table = [];
        foreach ($logs as $log) {
            $table[] = [
                $log->id,
                $log->status,
                $log->round?->round_code ?? '-',
                $log->response_time_ms ? "{$log->response_time_ms}ms" : '-',
                $log->retry_attempt,
                mb_substr($log->error_message ?? '-', 0, 40),
                $log->fetched_at?->format('Y-m-d H:i:s'),
            ];
        }

        $this->table(['ID', 'Status', 'Round', 'Time', 'Retry', 'Error', 'Fetched At'], $table);

        return self::SUCCESS;
    }

    private function showProviders(ResultSourceManager $manager): int
    {
        $providers = $manager->getRegisteredProviders();

        foreach ($providers as $name => $info) {
            $slugs = implode(', ', $info['supported_slugs']);
            $this->line("  <fg=green>{$name}</> → [{$slugs}]");
        }

        return self::SUCCESS;
    }

    private function cleanup(ResultSourceManager $manager): int
    {
        $days = (int) $this->option('days');
        $deleted = $manager->cleanOldLogs($days);
        $this->info("ลบ logs เก่ากว่า {$days} วัน: {$deleted} รายการ");

        return self::SUCCESS;
    }

    private function showHelp(): int
    {
        $this->info('Available actions:');
        $this->line('  list      - แสดง source ทั้งหมด');
        $this->line('  status    - ดูสถานะระบบ');
        $this->line('  health    - Health check');
        $this->line('  switch    - สลับ mode (auto/manual)');
        $this->line('  test      - ทดสอบ scrape');
        $this->line('  history   - ดูประวัติ fetch');
        $this->line('  providers - ดู providers ที่ลงทะเบียน');
        $this->line('  cleanup   - ล้าง logs เก่า');

        return self::SUCCESS;
    }
}
