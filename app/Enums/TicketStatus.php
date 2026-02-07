<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Pending = 'pending';
    case Won = 'won';
    case Lost = 'lost';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'รอผล',
            self::Won => 'ถูกรางวัล',
            self::Lost => 'ไม่ถูก',
            self::Cancelled => 'ยกเลิก',
            self::Refunded => 'คืนเงิน',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Won => 'green',
            self::Lost => 'red',
            self::Cancelled => 'gray',
            self::Refunded => 'blue',
        };
    }
}
