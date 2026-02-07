<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RiskControlApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
    }

    public function test_risk_dashboard_requires_admin(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Member,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/admin/risk/dashboard');

        $response->assertForbidden();
    }

    public function test_risk_dashboard_returns_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_set_user_win_rate_requires_reason(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/risk/users/{$user->id}/win-rate", [
                'win_rate' => 25.5,
                // missing reason
            ]);

        $response->assertUnprocessable();
    }

    public function test_risk_settings_returns_all_keys(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/settings');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_top_winners_accepts_period_parameter(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/top-winners?period=today&limit=10');

        $response->assertOk();
    }
}
