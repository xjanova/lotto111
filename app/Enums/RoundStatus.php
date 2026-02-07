<?php

namespace App\Enums;

enum RoundStatus: string
{
    case Upcoming = 'upcoming';
    case Open = 'open';
    case Closed = 'closed';
    case Resulted = 'resulted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'เร็วๆ นี้',
            self::Open => 'เปิดรับ',
            self::Closed => 'ปิดรับ',
            self::Resulted => 'ออกผลแล้ว',
            self::Cancelled => 'ยกเลิก',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Upcoming => 'blue',
            self::Open => 'green',
            self::Closed => 'orange',
            self::Resulted => 'gray',
            self::Cancelled => 'red',
        };
    }
}
