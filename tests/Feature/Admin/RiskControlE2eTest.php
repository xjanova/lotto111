<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RiskControlE2eTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_risk_dashboard_view_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/risk/dashboard');
        $response->assertOk();
    }

    public function test_risk_dashboard_json(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/risk/dashboard');
        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_live_stats(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/risk/live-stats');
        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_user_profiles(): void
    {
        User::factory()->count(3)->create(['role' => UserRole::Member]);

        $response = $this->actingAs($this->admin)->getJson('/admin/risk/users');
        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_user_profile_detail(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);

        $response = $this->actingAs($this->admin)
            ->getJson("/admin/risk/users/{$member->id}");
        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_set_win_rate(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/risk/users/{$member->id}/win-rate", [
                'win_rate' => 30.0,
                'reason' => 'ผู้เล่นชนะเยอะเกินไป',
            ]);

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_set_win_rate_validation(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/risk/users/{$member->id}/win-rate", [
                'win_rate' => 25.5,
            ]);

        $response->assertUnprocessable();
    }

    public function test_set_rate_adjustment(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/risk/users/{$member->id}/rate-adjustment", [
                'adjustment_percent' => -10,
                'reason' => 'ลดอัตราจ่าย',
            ]);

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_set_rate_adjustment_validation(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/risk/users/{$member->id}/rate-adjustment", [
                'adjustment_percent' => 60, // exceeds max 50
                'reason' => 'test',
            ]);

        $response->assertUnprocessable();
    }

    public function test_set_blocked_numbers(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/risk/users/{$member->id}/blocked-numbers", [
                'blocked_numbers' => ['123', '456', '789'],
            ]);

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_set_bet_limits(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/risk/users/{$member->id}/bet-limits", [
                'max_bet_per_ticket' => 5000,
                'max_bet_per_number' => 1000,
                'max_payout_per_day' => 100000,
                'max_payout_per_ticket' => 50000,
            ]);

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_risk_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/settings');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_update_risk_settings_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson('/admin/risk/settings', []);

        $response->assertUnprocessable();
    }

    public function test_run_auto_balance(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/risk/auto-balance');

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_top_winners(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/top-winners?period=today&limit=10');

        $response->assertOk()->assertJsonStructure(['success', 'data']);
    }

    public function test_top_losers(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/top-losers?period=today&limit=10');

        $response->assertOk()->assertJsonStructure(['success', 'data']);
    }

    public function test_number_exposure(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/number-exposure');

        $response->assertOk()->assertJsonStructure(['success', 'data']);
    }

    public function test_profit_snapshots(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/profit-snapshots?type=daily&limit=7');

        $response->assertOk()->assertJsonStructure(['success', 'data']);
    }

    public function test_alerts(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/alerts');

        $response->assertOk()->assertJsonStructure(['success', 'data']);
    }

    public function test_adjustment_logs(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/risk/adjustment-logs');

        $response->assertOk()->assertJsonStructure(['success', 'data']);
    }

    public function test_risk_dashboard_requires_admin(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);
        $this->actingAs($member)->get('/admin/risk/dashboard')->assertForbidden();
    }
}
