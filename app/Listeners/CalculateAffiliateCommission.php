<?php

namespace App\Listeners;

use App\Events\BetPlaced;
use App\Services\AffiliateService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculateAffiliateCommission implements ShouldQueue
{
    public string $queue = 'low';

    public function __construct(
        private AffiliateService $affiliateService,
    ) {}

    public function handle(BetPlaced $event): void
    {
        $this->affiliateService->calculateCommission($event->ticket);
    }
}
