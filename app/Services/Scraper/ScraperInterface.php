<?php

namespace App\Services\Scraper;

use App\Models\ResultSource;

/**
 * Interface สำหรับทุก Result Scraper Provider
 */
interface ScraperInterface
{
    /**
     * ดึงผลหวยจากแหล่งข้อมูลภายนอก
     *
     * @return ScraperResult ผลลัพธ์ที่ parsed แล้ว
     */
    public function fetch(ResultSource $source, ?string $date = null): ScraperResult;

    /**
     * ตรวจสอบว่า source นี้พร้อมใช้งานหรือไม่ (health check)
     */
    public function healthCheck(ResultSource $source): bool;

    /**
     * ชื่อ provider
     */
    public function getProviderName(): string;

    /**
     * รายการ lottery_type slugs ที่ provider นี้รองรับ
     */
    public function getSupportedSlugs(): array;
}
