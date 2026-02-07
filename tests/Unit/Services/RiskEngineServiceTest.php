<?php

namespace Tests\Unit\Services;

use App\Enums\RiskLevel;
use App\Services\Risk\RiskEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiskEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    private RiskEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RiskEngineService::class);
    }

    public function test_get_current_margin_returns_array(): void
    {
        $margin = $this->service->getCurrentMargin();

        $this->assertIsArray($margin);
        $this->assertArrayHasKey('total_bet', $margin);
        $this->assertArrayHasKey('total_payout', $margin);
        $this->assertArrayHasKey('gross_profit', $margin);
        $this->assertArrayHasKey('margin_percent', $margin);
    }

    public function test_risk_level_enum_has_correct_values(): void
    {
        $this->assertEquals('fish', RiskLevel::Fish->value);
        $this->assertEquals('normal', RiskLevel::Normal->value);
        $this->assertEquals('watch', RiskLevel::Watch->value);
        $this->assertEquals('danger', RiskLevel::Danger->value);
        $this->assertEquals('whale', RiskLevel::Whale->value);
    }

    public function test_get_effective_rate_returns_positive_float(): void
    {
        $user = \App\Models\User::factory()->create();
        $baseRate = 900.0;

        $effectiveRate = $this->service->getEffectiveRate($user, $baseRate);

        $this->assertIsFloat($effectiveRate);
        $this->assertGreaterThan(0, $effectiveRate);
    }

    public function test_recalculate_user_risk_returns_risk_level(): void
    {
        $user = \App\Models\User::factory()->create();

        $riskLevel = $this->service->recalculateUserRisk($user);

        $this->assertInstanceOf(RiskLevel::class, $riskLevel);
    }
}
