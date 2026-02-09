<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);

        // Seed some settings
        $this->seedSettings();
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'TestLotto', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_name_th', 'value' => 'ทดสอบ', 'group' => 'general', 'type' => 'string'],
            ['key' => 'min_deposit', 'value' => '100', 'group' => 'payment', 'type' => 'integer'],
            ['key' => 'max_deposit', 'value' => '100000', 'group' => 'payment', 'type' => 'integer'],
            ['key' => 'deposit_fee_type', 'value' => 'none', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'affiliate_enabled', 'value' => '1', 'group' => 'affiliate', 'type' => 'boolean'],
            ['key' => 'min_bet', 'value' => '1', 'group' => 'lottery', 'type' => 'integer'],
            ['key' => 'line_id', 'value' => '@test', 'group' => 'contact', 'type' => 'string'],
        ];

        foreach ($settings as $s) {
            Setting::create($s);
        }
    }

    public function test_settings_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');
        $response->assertOk();
        $response->assertSee('ตั้งค่าระบบ');
    }

    public function test_settings_view_has_data(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');
        $response->assertOk();
        $response->assertSee('TestLotto');
    }

    public function test_settings_json_returns_grouped(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/settings');
        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_update_settings_new_format(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson('/admin/settings', [
                'group' => 'general',
                'settings' => [
                    'site_name' => 'UpdatedLotto',
                    'site_name_th' => 'อัปเดต',
                ],
            ]);

        $response->assertOk()->assertJson(['success' => true, 'message' => 'บันทึกการตั้งค่าสำเร็จ']);
        $this->assertEquals('UpdatedLotto', Setting::where('key', 'site_name')->first()->value);
    }

    public function test_update_settings_old_format(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson('/admin/settings', [
                'settings' => [
                    ['key' => 'site_name', 'value' => 'OldFormat'],
                ],
            ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals('OldFormat', Setting::where('key', 'site_name')->first()->value);
    }

    public function test_update_fee_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson('/admin/settings', [
                'group' => 'fees',
                'settings' => [
                    'deposit_fee_type' => 'percent',
                ],
            ]);

        $response->assertOk();
        $this->assertEquals('percent', Setting::where('key', 'deposit_fee_type')->first()->value);
    }

    public function test_update_settings_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson('/admin/settings', []);

        $response->assertUnprocessable();
    }

    // ─── Logs ──────────────────────────────────

    public function test_logs_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/logs');
        $response->assertOk();
        $response->assertSee('บันทึกระบบ');
    }

    public function test_logs_json(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/logs');
        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_settings_requires_admin(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);
        $this->actingAs($member)->get('/admin/settings')->assertForbidden();
    }
}
