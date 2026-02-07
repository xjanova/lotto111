<?php

namespace App\Events;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: เงินเข้ากระเป๋าลูกค้าเรียบร้อย
 *
 * Broadcast ไปที่:
 * - User's wallet channel (แจ้งเตือน real-time)
 * - Admin dashboard (อัพเดทยอด)
 */
class DepositCredited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Deposit $deposit,
        public readonly User $user,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id . '.wallet'),
            new PrivateChannel('admin.sms-deposits'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'deposit.credited';
    }

    public function broadcastWith(): array
    {
        return [
            'deposit_id' => $this->deposit->id,
            'user_id' => $this->user->id,
            'amount' => $this->deposit->amount,
            'new_balance' => $this->user->fresh()->balance ?? 0,
            'bank' => $this->deposit->matched_bank,
            'timestamp' => now()->toIso8601String(),
            'message' => "ฝากเงิน ฿" . number_format((float) $this->deposit->amount, 2) . " สำเร็จ",
        ];
    }
}
