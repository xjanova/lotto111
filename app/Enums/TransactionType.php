<?php

namespace App\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdraw = 'withdraw';
    case Bet = 'bet';
    case Win = 'win';
    case Refund = 'refund';
    case Commission = 'commission';
    case Adjustment = 'adjustment';
    case Bonus = 'bonus';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'ฝากเงิน',
            self::Withdraw => 'ถอนเงิน',
            self::Bet => 'แทงหวย',
            self::Win => 'ถูกรางวัล',
            self::Refund => 'คืนเงิน',
            self::Commission => 'คอมมิชชั่น',
            self::Adjustment => 'ปรับปรุง',
            self::Bonus => 'โบนัส',
        };
    }

    public function isCredit(): bool
    {
        return in_array($this, [self::Deposit, self::Win, self::Refund, self::Commission, self::Bonus]);
    }
}
