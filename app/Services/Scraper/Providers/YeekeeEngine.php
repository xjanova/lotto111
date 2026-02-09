<?php

namespace App\Services\Scraper\Providers;

use App\Models\LotteryRound;
use App\Models\ResultSource;
use App\Models\YeekeeSubmission;
use App\Services\Scraper\AbstractScraper;
use App\Services\Scraper\ScraperResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Yeekee Result Engine (ยี่กี)
 *
 * ยี่กีไม่ได้ดึงจากเว็บภายนอก แต่คำนวณจากเลขที่ user ส่งมา
 *
 * วิธีคำนวณ (แบบมาตรฐาน):
 *   1. User ส่งเลข 5 หลักเข้ามา (ยิงเลข)
 *   2. รวมเลขทั้งหมดที่ส่งมาในรอบนั้น
 *   3. นำผลรวมมาตัด:
 *      - 3 ตัวบน = 3 หลักสุดท้ายของผลรวม
 *      - 2 ตัวล่าง = หลักพันและหลักหมื่นของผลรวม
 *   4. ถ้ามีเลขส่งไม่ถึง minimum → ใช้ random seed เสริม
 *
 * ออกทุก 10-15 นาที, 88-144 รอบ/วัน
 */
class YeekeeEngine extends AbstractScraper
{
    /** จำนวนเลขขั้นต่ำที่ต้องมีก่อนคำนวณ */
    private const MIN_SUBMISSIONS = 3;

    /** Maximum value สำหรับ random seed */
    private const RANDOM_MAX = 99999;

    public function getProviderName(): string
    {
        return 'yeekee_internal';
    }

    public function getSupportedSlugs(): array
    {
        return ['yeekee'];
    }

    /**
     * "Fetch" สำหรับ yeekee = คำนวณผลจากเลขที่ user ส่งมา
     */
    public function fetch(ResultSource $source, ?string $date = null): ScraperResult
    {
        return ScraperResult::failed('Yeekee uses calculateResult() instead of fetch()');
    }

    /**
     * คำนวณผล Yeekee สำหรับรอบที่ระบุ
     */
    public function calculateResult(LotteryRound $round): ScraperResult
    {
        $submissions = YeekeeSubmission::where('lottery_round_id', $round->id)
            ->orderBy('sequence')
            ->get();

        $submittedNumbers = $submissions->pluck('number')->toArray();
        $totalSubmissions = count($submittedNumbers);

        Log::info("Yeekee: Calculating result for round {$round->round_code}, {$totalSubmissions} submissions");

        // ถ้าไม่มีเลขส่งมาเลย → ใช้ random ทั้งหมด
        if ($totalSubmissions === 0) {
            return $this->generateRandomResult($round);
        }

        // คำนวณผลรวม
        $sum = 0;
        foreach ($submittedNumbers as $num) {
            $sum += (int) $num;
        }

        // ถ้าเลขน้อยกว่า minimum → เพิ่ม random seed เข้าไป
        if ($totalSubmissions < self::MIN_SUBMISSIONS) {
            $needed = self::MIN_SUBMISSIONS - $totalSubmissions;
            for ($i = 0; $i < $needed; $i++) {
                $seed = $this->generateSecureRandom();
                $sum += $seed;
            }
            Log::info("Yeekee: Added {$needed} random seeds (total submissions: {$totalSubmissions})");
        }

        return $this->buildResultFromSum($sum, $round, $submittedNumbers);
    }

    /**
     * สร้างผลจาก random (กรณีไม่มี user ส่งเลขมา)
     */
    private function generateRandomResult(LotteryRound $round): ScraperResult
    {
        $seeds = [];
        $sum = 0;
        for ($i = 0; $i < self::MIN_SUBMISSIONS; $i++) {
            $seed = $this->generateSecureRandom();
            $seeds[] = $seed;
            $sum += $seed;
        }

        Log::info("Yeekee: No submissions, using pure random for round {$round->round_code}");

        return $this->buildResultFromSum($sum, $round, $seeds);
    }

    /**
     * คำนวณผลจากผลรวม
     */
    private function buildResultFromSum(int $sum, LotteryRound $round, array $numbers): ScraperResult
    {
        $sumStr = str_pad((string) abs($sum), 6, '0', STR_PAD_LEFT);

        // 3 ตัวบน = 3 หลักสุดท้ายของผลรวม
        $threeTop = substr($sumStr, -3);

        // 2 ตัวล่าง = หลักที่ 4 และ 5 จากท้าย (หลักพันและหลักหมื่น)
        $len = strlen($sumStr);
        $twoBottom = substr($sumStr, max(0, $len - 5), 2);
        if (strlen($twoBottom) < 2) {
            $twoBottom = str_pad($twoBottom, 2, '0', STR_PAD_LEFT);
        }

        // 2 ตัวบน = 2 หลักสุดท้ายของผลรวม (สำหรับ bet type: two_top)
        $twoTop = substr($sumStr, -2);

        $results = [
            'three_top' => $threeTop,
            'two_top' => $twoTop,
            'two_bottom' => $twoBottom,
            'sum' => $sum,
        ];

        return ScraperResult::success(
            results: $results,
            rawData: [
                'sum' => $sum,
                'total_numbers' => count($numbers),
                'numbers' => array_slice($numbers, 0, 50), // เก็บแค่ 50 ตัวแรก
                'round_code' => $round->round_code,
            ],
            drawDate: $round->close_at?->format('Y-m-d') ?? date('Y-m-d'),
            drawTime: $round->close_at?->format('H:i:s'),
        );
    }

    /**
     * User ส่งเลขเข้ามา (ยิงเลข)
     */
    public function submitNumber(LotteryRound $round, int $userId, string $number): YeekeeSubmission
    {
        // หา sequence ถัดไป
        $nextSequence = YeekeeSubmission::where('lottery_round_id', $round->id)->max('sequence') + 1;

        return YeekeeSubmission::create([
            'lottery_round_id' => $round->id,
            'user_id' => $userId,
            'number' => str_pad($number, 5, '0', STR_PAD_LEFT),
            'sequence' => $nextSequence,
            'created_at' => now(),
        ]);
    }

    /**
     * ดูเลขที่ส่งมาแล้วในรอบ
     */
    public function getSubmissions(LotteryRound $round): array
    {
        return YeekeeSubmission::where('lottery_round_id', $round->id)
            ->with('user:id,name')
            ->orderBy('sequence')
            ->get()
            ->toArray();
    }

    /**
     * สร้างเลข random ที่ปลอดภัย
     */
    private function generateSecureRandom(): int
    {
        return random_int(10000, self::RANDOM_MAX);
    }

    /**
     * Health check สำหรับ Yeekee = เช็คว่า DB ทำงานปกติ
     */
    public function healthCheck(ResultSource $source): bool
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
