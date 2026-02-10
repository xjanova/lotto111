<?php

namespace App\Enums;

enum DepositStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Credited = 'credited';
    case WaitingTransfer = 'waiting_transfer';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'รอตรวจสอบ',
            self::Approved => 'อนุมัติ',
            self::Credited => 'เติมเงินแล้ว',
            self::WaitingTransfer => 'รอโอน',
            self::Rejected => 'ปฏิเสธ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved, self::Credited => 'green',
            self::WaitingTransfer => 'blue',
            self::Rejected => 'red',
        };
    }
}
