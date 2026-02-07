<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';
    case Emergency = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Info => 'ข้อมูล',
            self::Warning => 'แจ้งเตือน',
            self::Critical => 'วิกฤต',
            self::Emergency => 'ฉุกเฉิน',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Info => 'blue',
            self::Warning => 'yellow',
            self::Critical => 'red',
            self::Emergency => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Info => 'information-circle',
            self::Warning => 'exclamation-triangle',
            self::Critical => 'x-circle',
            self::Emergency => 'fire',
        };
    }

    public function shouldNotifyImmediately(): bool
    {
        return in_array($this, [self::Critical, self::Emergency]);
    }
}
