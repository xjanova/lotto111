<?php

namespace App\Enums;

enum RiskLevel: string
{
    case Fish = 'fish';
    case Normal = 'normal';
    case Watch = 'watch';
    case Danger = 'danger';
    case Whale = 'whale';

    public function label(): string
    {
        return match ($this) {
            self::Fish => 'ปลา (เสียสุทธิ)',
            self::Normal => 'ปกติ',
            self::Watch => 'ระวัง',
            self::Danger => 'อันตราย (ได้เยอะ)',
            self::Whale => 'VIP Whale',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Fish => '🟢',
            self::Normal => '🟡',
            self::Watch => '🟠',
            self::Danger => '🔴',
            self::Whale => '⚫',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Fish => 'green',
            self::Normal => 'yellow',
            self::Watch => 'orange',
            self::Danger => 'red',
            self::Whale => 'purple',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Fish => 'bg-green-100 text-green-800',
            self::Normal => 'bg-yellow-100 text-yellow-800',
            self::Watch => 'bg-orange-100 text-orange-800',
            self::Danger => 'bg-red-100 text-red-800',
            self::Whale => 'bg-purple-100 text-purple-800',
        };
    }
}
