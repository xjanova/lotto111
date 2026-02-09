<?php

namespace App\Jobs;

use App\Models\LotteryRound;
use App\Services\ResultService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLotteryResult implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(
        public LotteryRound $round,
        public array $results,
    ) {}

    public function handle(ResultService $resultService): void
    {
        $resultService->submitResults($this->round, $this->results);
    }
}
