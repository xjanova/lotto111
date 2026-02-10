<?php

namespace App\Events;

use App\Enums\VipLevel;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLevelUp
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public VipLevel $oldLevel,
        public VipLevel $newLevel,
    ) {}
}
