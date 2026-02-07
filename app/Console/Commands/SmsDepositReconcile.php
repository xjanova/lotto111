<?php

namespace App\Console\Commands;

use App\Services\Deposit\SmsDepositService;
use App\Services\Deposit\SmsPaymentProcessorService;
use Illuminate\Console\Command;

/**
 * Command: Reconcile SMS Deposits & Cleanup
 *
 * ทำงานทุก 5 นาทีผ่าน Scheduler:
 * 1. หมดอายุรายการที่เกินเวลา
 * 2. ตรวจสอบรายการค้าง
 * 3. ลบ nonces เก่า
 * 4. ลบ unique amounts หมดอายุ
 */
class SmsDepositReconcile extends Command
{
    protected $signature = 'sms-deposit:reconcile';
    protected $description = 'Reconcile SMS deposits and cleanup expired data';

    public function handle(SmsDepositService $depositService, SmsPaymentProcessorService $smsProcessor): int
    {
        $this->info('Starting SMS Deposit reconciliation...');

        // Reconcile deposits
        $stats = $depositService->reconcile();
        $this->info("Deposits: {$stats['expired']} expired, {$stats['orphaned']} orphaned SMS");

        // Cleanup SMS payment data
        $smsProcessor->cleanup();
        $this->info('SMS payment data cleaned up');

        $this->info('SMS Deposit reconciliation completed.');

        return self::SUCCESS;
    }
}
