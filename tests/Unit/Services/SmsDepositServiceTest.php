<?php

namespace Tests\Unit\Services;

use App\Enums\SmsDepositStatus;
use App\Services\Deposit\SmsDepositService;
use Tests\TestCase;

class SmsDepositServiceTest extends TestCase
{
    private SmsDepositService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SmsDepositService::class);
    }

    public function test_sms_deposit_status_enum_labels(): void
    {
        $this->assertEquals('รอโอนเงิน', SmsDepositStatus::WaitingTransfer->label());
        $this->assertEquals('เติมเงินแล้ว', SmsDepositStatus::Credited->label());
        $this->assertEquals('หมดอายุ', SmsDepositStatus::Expired->label());
    }

    public function test_sms_deposit_status_is_terminal(): void
    {
        $this->assertTrue(SmsDepositStatus::Credited->isTerminal());
        $this->assertTrue(SmsDepositStatus::Expired->isTerminal());
        $this->assertTrue(SmsDepositStatus::Cancelled->isTerminal());
        $this->assertTrue(SmsDepositStatus::Failed->isTerminal());

        $this->assertFalse(SmsDepositStatus::WaitingTransfer->isTerminal());
        $this->assertFalse(SmsDepositStatus::Matching->isTerminal());
    }

    public function test_sms_deposit_status_is_pending(): void
    {
        $this->assertTrue(SmsDepositStatus::WaitingTransfer->isPending());
        $this->assertTrue(SmsDepositStatus::Matching->isPending());

        $this->assertFalse(SmsDepositStatus::Credited->isPending());
    }

    public function test_validate_deposit_limits_rejects_below_minimum(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->createDeposit($user, 10); // min is 100
    }

    public function test_validate_deposit_limits_rejects_above_maximum(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->createDeposit($user, 999999); // max is 50000
    }
}
