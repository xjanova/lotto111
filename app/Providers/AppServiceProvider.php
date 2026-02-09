<?php

namespace App\Providers;

use App\Events\BetPlaced;
use App\Events\ResultAnnounced;
use App\Listeners\CalculateAffiliateCommission;
use App\Listeners\SendResultNotifications;
use App\Listeners\UpdateGamificationOnBet;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Event-Listener bindings
        Event::listen(BetPlaced::class, CalculateAffiliateCommission::class);
        Event::listen(BetPlaced::class, UpdateGamificationOnBet::class);
        Event::listen(ResultAnnounced::class, SendResultNotifications::class);
    }
}
