<?php

namespace App\Console\Commands;

use App\Enums\RoundStatus;
use App\Jobs\ProcessLotteryResult;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use App\Services\Scraper\ResultSourceManager;
use Illuminate\Console\Command;

/**
 * คำนวณและออกผล Yeekee สำหรับรอบที่ปิดแล้ว
 *
 * Usage:
 *   php artisan lottery:process-yeekee              # ประมวลผลทุกรอบที่ปิดแล้ว
 *   php artisan lottery:process-yeekee --round=123  # ประมวลผลรอบเฉพาะ
 */
class ProcessYeekeeRound extends Command
{
    protected $signature = 'lottery:process-yeekee
        {--round= : Round ID เฉพาะ}';

    protected $description = 'คำนวณผล Yeekee จากเลขที่ user ส่งมา';

    public function handle(ResultSourceManager $manager): int
    {
        // ประมวลผลรอบเฉพาะ
        if ($roundId = $this->option('round')) {
            $round = LotteryRound::find($roundId);
            if (! $round) {
                $this->error("ไม่พบรอบ ID: {$roundId}");

                return self::FAILURE;
            }

            return $this->processRound($manager, $round);
        }

        // ประมวลผลทุกรอบ Yeekee ที่ปิดแล้ว
        $yeekeeType = LotteryType::where('slug', 'yeekee')->first();
        if (! $yeekeeType) {
            $this->warn('ไม่พบประเภท Yeekee');

            return self::FAILURE;
        }

        $closedRounds = LotteryRound::where('lottery_type_id', $yeekeeType->id)
            ->where('status', RoundStatus::Closed)
            ->whereNull('result_at')
            ->orderBy('close_at')
            ->get();

        if ($closedRounds->isEmpty()) {
            $this->info('ไม่มีรอบ Yeekee ที่ต้องประมวลผล');

            return self::SUCCESS;
        }

        $this->info("พบ {$closedRounds->count()} รอบที่ต้องประมวลผล");

        $success = 0;
        foreach ($closedRounds as $round) {
            if ($this->processRound($manager, $round) === self::SUCCESS) {
                $success++;
            }
        }

        $this->info("ประมวลผลสำเร็จ {$success}/{$closedRounds->count()} รอบ");

        return self::SUCCESS;
    }

    private function processRound(ResultSourceManager $manager, LotteryRound $round): int
    {
        $this->info("ประมวลผลรอบ: {$round->round_code}");

        $result = $manager->calculateYeekeeResult($round);

        if ($result->success) {
            $this->info("  ✓ three_top={$result->getResult('three_top')}, two_bottom={$result->getResult('two_bottom')}");

            // ส่งไป process (ตรวจสอบโพยและจ่ายรางวัล)
            ProcessLotteryResult::dispatch($round, $result->results);

            return self::SUCCESS;
        }

        $this->error("  ✗ {$result->error}");

        return self::FAILURE;
    }
}
