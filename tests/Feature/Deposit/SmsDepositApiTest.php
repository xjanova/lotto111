<?php

namespace Tests\Feature\Deposit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsDepositApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_info_returns_bank_details(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/deposit/info');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'bank' => ['name', 'account_number', 'account_name'],
                    'promptpay' => ['number'],
                    'limits' => ['min_amount', 'max_amount', 'daily_limit'],
                    'supported_banks',
                ],
            ]);
    }

    public function test_create_sms_deposit_requires_auth(): void
    {
        $response = $this->postJson('/api/deposit/sms', [
            'amount' => 500,
        ]);

        $response->assertUnauthorized();
    }

    public function test_create_sms_deposit_validates_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/deposit/sms', [
                'amount' => 'invalid',
            ]);

        $response->assertUnprocessable();
    }

    public function test_deposit_history_returns_paginated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/deposit/history');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_cancel_nonexistent_deposit_returns_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/deposit/99999/cancel');

        $response->assertUnprocessable();
    }
}
