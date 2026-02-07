<?php

namespace App\Enums;

enum BadgeRarity: string
{
    case Common = 'common';
    case Rare = 'rare';
    case Epic = 'epic';
    case Legendary = 'legendary';

    public function label(): string
    {
        return match ($this) {
            self::Common => 'ทั่วไป',
            self::Rare => 'หายาก',
            self::Epic => 'มหากาพย์',
            self::Legendary => 'ตำนาน',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Common => '#9ca3af',
            self::Rare => '#3b82f6',
            self::Epic => '#a855f7',
            self::Legendary => '#f59e0b',
        };
    }
}
