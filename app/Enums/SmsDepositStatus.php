<?php

namespace App\Enums;

enum SmsDepositStatus: string
{
    case WaitingTransfer = 'waiting_transfer';
    case Matching = 'matching';
    case Matched = 'matched';
    case Credited = 'credited';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::WaitingTransfer => 'รอโอนเงิน',
            self::Matching => 'กำลังจับคู่',
            self::Matched => 'จับคู่สำเร็จ',
            self::Credited => 'เติมเงินแล้ว',
            self::Expired => 'หมดอายุ',
            self::Cancelled => 'ยกเลิก',
            self::Failed => 'ล้มเหลว',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WaitingTransfer => 'yellow',
            self::Matching => 'blue',
            self::Matched => 'indigo',
            self::Credited => 'green',
            self::Expired => 'gray',
            self::Cancelled => 'gray',
            self::Failed => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WaitingTransfer => '⏳',
            self::Matching => '🔄',
            self::Matched => '✅',
            self::Credited => '💰',
            self::Expired => '⏰',
            self::Cancelled => '❌',
            self::Failed => '⚠️',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Credited, self::Expired, self::Cancelled, self::Failed]);
    }

    public function isPending(): bool
    {
        return in_array($this, [self::WaitingTransfer, self::Matching]);
    }
}
