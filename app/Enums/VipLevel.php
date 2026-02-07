<?php

namespace App\Enums;

enum VipLevel: string
{
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';
    case Diamond = 'diamond';

    public function label(): string
    {
        return match ($this) {
            self::Bronze => 'Bronze',
            self::Silver => 'Silver',
            self::Gold => 'Gold',
            self::Platinum => 'Platinum',
            self::Diamond => 'Diamond',
        };
    }

    public function minXp(): int
    {
        return match ($this) {
            self::Bronze => 0,
            self::Silver => 1_000,
            self::Gold => 5_000,
            self::Platinum => 20_000,
            self::Diamond => 100_000,
        };
    }

    public function discountRate(): float
    {
        return match ($this) {
            self::Bronze => 0,
            self::Silver => 1.0,
            self::Gold => 2.0,
            self::Platinum => 3.0,
            self::Diamond => 5.0,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Bronze => '#cd7f32',
            self::Silver => '#c0c0c0',
            self::Gold => '#ffd700',
            self::Platinum => '#e5e4e2',
            self::Diamond => '#b9f2ff',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Bronze => 'shield',
            self::Silver => 'shield-check',
            self::Gold => 'star',
            self::Platinum => 'crown',
            self::Diamond => 'gem',
        };
    }

    public static function fromXp(int $xp): self
    {
        return match (true) {
            $xp >= 100_000 => self::Diamond,
            $xp >= 20_000 => self::Platinum,
            $xp >= 5_000 => self::Gold,
            $xp >= 1_000 => self::Silver,
            default => self::Bronze,
        };
    }
}
