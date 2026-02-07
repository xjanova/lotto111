<?php

namespace App\Enums;

enum DepositStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'รอตรวจสอบ',
            self::Approved => 'อนุมัติ',
            self::Rejected => 'ปฏิเสธ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }
}
