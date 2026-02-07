<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\LotteryCategory;
use App\Enums\RoundStatus;
use App\Enums\TicketStatus;
use App\Enums\TransactionType;
use App\Enums\DepositMethod;
use App\Enums\DepositStatus;
use App\Enums\RiskLevel;
use App\Enums\AlertSeverity;
use App\Enums\SmsDepositStatus;
use Tests\TestCase;

class EnumTest extends TestCase
{
    public function test_user_role_is_admin(): void
    {
        $this->assertTrue(UserRole::Admin->isAdmin());
        $this->assertTrue(UserRole::SuperAdmin->isAdmin());
        $this->assertFalse(UserRole::Member->isAdmin());
    }

    public function test_user_status_has_color(): void
    {
        $this->assertNotEmpty(UserStatus::Active->color());
        $this->assertNotEmpty(UserStatus::Banned->color());
    }

    public function test_lottery_category_values(): void
    {
        $cases = LotteryCategory::cases();
        $this->assertGreaterThan(0, count($cases));
    }

    public function test_round_status_values(): void
    {
        $this->assertEquals('open', RoundStatus::Open->value);
        $this->assertEquals('closed', RoundStatus::Closed->value);
    }

    public function test_ticket_status_values(): void
    {
        $this->assertEquals('won', TicketStatus::Won->value);
        $this->assertEquals('lost', TicketStatus::Lost->value);
    }

    public function test_transaction_type_is_credit(): void
    {
        $this->assertTrue(TransactionType::Deposit->isCredit());
        $this->assertTrue(TransactionType::Win->isCredit());
        $this->assertFalse(TransactionType::Bet->isCredit());
        $this->assertFalse(TransactionType::Withdraw->isCredit());
    }

    public function test_risk_level_has_emoji(): void
    {
        $this->assertNotEmpty(RiskLevel::Fish->emoji());
        $this->assertNotEmpty(RiskLevel::Whale->emoji());
    }

    public function test_alert_severity_should_notify(): void
    {
        $this->assertTrue(AlertSeverity::Emergency->shouldNotifyImmediately());
        $this->assertTrue(AlertSeverity::Critical->shouldNotifyImmediately());
        $this->assertFalse(AlertSeverity::Info->shouldNotifyImmediately());
    }

    public function test_sms_deposit_status_has_all_cases(): void
    {
        $cases = SmsDepositStatus::cases();
        $this->assertCount(7, $cases);
    }
}
