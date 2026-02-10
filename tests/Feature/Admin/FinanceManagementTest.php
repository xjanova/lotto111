<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    // ─── Deposits ──────────────────────────────────

    public function test_deposits_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/finance/deposits');
        $response->assertOk();
        $response->assertSee('ฝากเงิน');
    }

    public function test_deposits_json(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member]);
        Deposit::create([
            'user_id' => $user->id,
            'amount' => 500,
            'unique_amount' => 500.01,
            'method' => 'manual',
            'status' => 'pending',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->admin)->getJson('/admin/finance/deposits');
        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_approve_deposit(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member, 'balance' => 0]);
        $deposit = Deposit::create([
            'user_id' => $user->id,
            'amount' => 500,
            'unique_amount' => 500.01,
            'method' => 'manual',
            'status' => 'pending',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/finance/deposits/{$deposit->id}/approve");

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals('credited', $deposit->fresh()->status);
        $this->assertGreaterThan(0, $user->fresh()->balance);
    }

    public function test_reject_deposit(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member]);
        $deposit = Deposit::create([
            'user_id' => $user->id,
            'amount' => 500,
            'unique_amount' => 500.02,
            'method' => 'manual',
            'status' => 'pending',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/finance/deposits/{$deposit->id}/reject", [
                'reason' => 'Invalid slip',
            ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals('rejected', $deposit->fresh()->status);
    }

    public function test_cannot_approve_already_processed_deposit(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member]);
        $deposit = Deposit::create([
            'user_id' => $user->id,
            'amount' => 500,
            'unique_amount' => 500.03,
            'method' => 'manual',
            'status' => 'credited',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/finance/deposits/{$deposit->id}/approve");

        $response->assertStatus(422);
    }

    // ─── Withdrawals ──────────────────────────────────

    public function test_withdrawals_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/finance/withdrawals');
        $response->assertOk();
        $response->assertSee('ถอนเงิน');
    }

    public function test_withdrawals_json(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/finance/withdrawals');
        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    // ─── Report ──────────────────────────────────

    public function test_report_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/finance/report');
        $response->assertOk();
        $response->assertSee('รายงาน');
    }

    public function test_report_json_structure(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/finance/report');
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period' => ['from', 'to'],
                    'summary' => ['deposits', 'withdrawals', 'bets', 'wins', 'profit', 'margin'],
                ],
            ]);
    }

    public function test_report_with_date_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/finance/report?from=2026-01-01&to=2026-01-31');
        $response->assertOk();
    }

    // ─── Auth guard ──────────────────────────────────

    public function test_finance_requires_admin(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);

        $this->actingAs($member)->get('/admin/finance/deposits')->assertForbidden();
        $this->actingAs($member)->get('/admin/finance/withdrawals')->assertForbidden();
        $this->actingAs($member)->get('/admin/finance/report')->assertForbidden();
    }
}
