<?php

namespace App\Services\Scraper;

/**
 * DTO สำหรับผลลัพธ์จากการ scrape
 *
 * ผลลัพธ์ที่ได้จะถูก map เป็น result_type => result_value
 * ตาม format ที่ ResultService ต้องการ:
 *   three_top => "123"
 *   two_bottom => "45"
 *   three_bottom => "678"  (ถ้ามี)
 *   etc.
 */
class ScraperResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $results = [],       // ['three_top' => '123', 'two_bottom' => '45', ...]
        public readonly ?string $error = null,
        public readonly ?array $rawData = null,     // ข้อมูลดิบจาก source
        public readonly ?string $drawDate = null,   // วันที่ออกผล (Y-m-d)
        public readonly ?string $drawTime = null,   // เวลาที่ออกผล (H:i:s)
        public readonly ?string $sourceUrl = null,
        public readonly int $responseTimeMs = 0,
    ) {}

    public static function success(
        array $results,
        ?array $rawData = null,
        ?string $drawDate = null,
        ?string $drawTime = null,
        ?string $sourceUrl = null,
        int $responseTimeMs = 0,
    ): static {
        return new static(
            success: true,
            results: $results,
            rawData: $rawData,
            drawDate: $drawDate,
            drawTime: $drawTime,
            sourceUrl: $sourceUrl,
            responseTimeMs: $responseTimeMs,
        );
    }

    public static function failed(string $error, ?string $sourceUrl = null, int $responseTimeMs = 0): static
    {
        return new static(
            success: false,
            error: $error,
            sourceUrl: $sourceUrl,
            responseTimeMs: $responseTimeMs,
        );
    }

    public static function noData(?string $sourceUrl = null): static
    {
        return new static(
            success: false,
            error: 'ยังไม่มีผลรางวัล',
            sourceUrl: $sourceUrl,
        );
    }

    public function hasResult(string $type): bool
    {
        return isset($this->results[$type]) && $this->results[$type] !== '';
    }

    public function getResult(string $type): ?string
    {
        return $this->results[$type] ?? null;
    }

    /**
     * ตรวจสอบว่ามี result ครบตาม required types หรือไม่
     */
    public function hasAllRequired(array $requiredTypes): bool
    {
        foreach ($requiredTypes as $type) {
            if (! $this->hasResult($type)) {
                return false;
            }
        }

        return true;
    }
}
