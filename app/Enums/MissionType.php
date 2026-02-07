<?php

namespace App\Enums;

enum MissionType: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Special = 'special';
    case Achievement = 'achievement';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'ภารกิจรายวัน',
            self::Weekly => 'ภารกิจรายสัปดาห์',
            self::Special => 'ภารกิจพิเศษ',
            self::Achievement => 'ความสำเร็จ',
        };
    }

    public function resetPeriod(): ?string
    {
        return match ($this) {
            self::Daily => 'daily',
            self::Weekly => 'weekly',
            self::Special => null,
            self::Achievement => null,
        };
    }
}
