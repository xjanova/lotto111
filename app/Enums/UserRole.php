<?php

namespace App\Enums;

enum UserRole: string
{
    case Member = 'member';
    case Agent = 'agent';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'สมาชิก',
            self::Agent => 'ตัวแทน',
            self::Admin => 'แอดมิน',
            self::SuperAdmin => 'ซูเปอร์แอดมิน',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin]);
    }
}
