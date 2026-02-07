<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Banned = 'banned';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'ปกติ',
            self::Suspended => 'ระงับชั่วคราว',
            self::Banned => 'แบน',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Suspended => 'yellow',
            self::Banned => 'red',
        };
    }
}
