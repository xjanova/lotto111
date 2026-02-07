<?php

namespace Tests\Unit\Services;

use App\Enums\VipLevel;
use App\Enums\BadgeRarity;
use App\Enums\MissionType;
use Tests\TestCase;

class GamificationServiceTest extends TestCase
{
    public function test_vip_level_from_xp(): void
    {
        $this->assertEquals(VipLevel::Bronze, VipLevel::fromXp(0));
        $this->assertEquals(VipLevel::Bronze, VipLevel::fromXp(999));
    }

    public function test_vip_level_has_min_xp(): void
    {
        $this->assertEquals(0, VipLevel::Bronze->minXp());
        $this->assertGreaterThan(0, VipLevel::Silver->minXp());
    }

    public function test_badge_rarity_has_colors(): void
    {
        $this->assertNotEmpty(BadgeRarity::Common->color());
        $this->assertNotEmpty(BadgeRarity::Legendary->color());
    }

    public function test_badge_rarity_has_labels(): void
    {
        $this->assertEquals('ทั่วไป', BadgeRarity::Common->label());
        $this->assertEquals('ตำนาน', BadgeRarity::Legendary->label());
    }

    public function test_mission_type_has_reset_period(): void
    {
        $this->assertNotEmpty(MissionType::Daily->resetPeriod());
        $this->assertNotEmpty(MissionType::Weekly->resetPeriod());
    }
}
