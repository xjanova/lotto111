<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Deposit;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->getJson('/admin')->assertUnauthorized();
    }

    public function test_dashboard_requires_admin_role(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);
        $this->actingAs($member)->get('/admin')->assertForbidden();
    }

    public function test_dashboard_loads_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertOk();
        $response->assertSee('แดชบอร์ด');
    }

    public function test_dashboard_returns_json_for_api(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin');
        $response->assertOk()
            ->assertJsonStructure(['success', 'data' => ['stats', 'chartData', 'recentDeposits']]);
    }

    public function test_dashboard_shows_stats(): void
    {
        // Create test data
        User::factory()->count(3)->create(['role' => UserRole::Member]);

        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertOk();
        $response->assertSee('สมาชิก');
        $response->assertSee('ฝากวันนี้');
        $response->assertSee('กำไร/ขาดทุน');
    }

    public function test_dashboard_chart_data_structure(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin');
        $response->assertOk();

        $data = $response->json('data.chartData');
        $this->assertArrayHasKey('revenue', $data);
        $this->assertArrayHasKey('members', $data);
        $this->assertArrayHasKey('deposit_withdraw', $data);
        $this->assertArrayHasKey('lottery_types', $data);

        $this->assertArrayHasKey('labels', $data['revenue']);
        $this->assertArrayHasKey('deposits', $data['revenue']);
        $this->assertCount(7, $data['revenue']['labels']);
    }

    public function test_admin_logout(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/logout');
        $response->assertRedirect('/admin/login');
    }
}
