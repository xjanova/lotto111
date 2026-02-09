<?php

namespace App\Services\Scraper;

use App\Models\ResultFetchLog;
use App\Models\ResultSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Base class สำหรับทุก scraper - รวม common logic:
 * - HTTP client with timeout/retry
 * - Logging
 * - Error handling
 * - User-Agent rotation
 */
abstract class AbstractScraper implements ScraperInterface
{
    /** User agents สำหรับหมุนเวียน เพื่อไม่ให้ถูก block */
    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
    ];

    /**
     * ดึงผลด้วย retry logic
     */
    public function fetchWithRetry(ResultSource $source, ?string $date = null): ScraperResult
    {
        $maxRetries = $source->retry_count;
        $delay = $source->retry_delay_seconds;
        $lastResult = null;

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            if ($attempt > 0) {
                sleep(min($delay * $attempt, 120)); // exponential backoff, max 2 minutes
            }

            try {
                $result = $this->fetch($source, $date);

                // Log the fetch
                ResultFetchLog::logFetch(
                    sourceId: $source->id,
                    status: $result->success ? 'success' : 'no_data',
                    url: $result->sourceUrl,
                    rawResponse: $result->rawData,
                    parsedResults: $result->results,
                    error: $result->error,
                    responseTimeMs: $result->responseTimeMs,
                    retryAttempt: $attempt,
                );

                if ($result->success) {
                    $source->markSuccess();
                    return $result;
                }

                $lastResult = $result;
            } catch (\Throwable $e) {
                Log::warning("Scraper [{$this->getProviderName()}] attempt {$attempt} failed: {$e->getMessage()}");

                ResultFetchLog::logFetch(
                    sourceId: $source->id,
                    status: 'failed',
                    url: $source->source_url,
                    error: $e->getMessage(),
                    retryAttempt: $attempt,
                );

                $lastResult = ScraperResult::failed($e->getMessage(), $source->source_url);
            }
        }

        $source->markFailed($lastResult?->error ?? 'Max retries exceeded');

        return $lastResult ?? ScraperResult::failed('Max retries exceeded');
    }

    /**
     * HTTP GET request with proper headers
     */
    protected function httpGet(string $url, int $timeout = 30, array $headers = []): \Illuminate\Http\Client\Response
    {
        return Http::timeout($timeout)
            ->withHeaders(array_merge([
                'User-Agent' => $this->getRandomUserAgent(),
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'th-TH,th;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control' => 'no-cache',
            ], $headers))
            ->get($url);
    }

    /**
     * HTTP POST request
     */
    protected function httpPost(string $url, array $data = [], int $timeout = 30, array $headers = []): \Illuminate\Http\Client\Response
    {
        return Http::timeout($timeout)
            ->withHeaders(array_merge([
                'User-Agent' => $this->getRandomUserAgent(),
                'Accept' => 'application/json',
                'Accept-Language' => 'th-TH,th;q=0.9',
            ], $headers))
            ->post($url, $data);
    }

    /**
     * สุ่ม User-Agent
     */
    protected function getRandomUserAgent(): string
    {
        return $this->userAgents[array_rand($this->userAgents)];
    }

    /**
     * Extract text between two markers in HTML
     */
    protected function extractBetween(string $html, string $start, string $end): ?string
    {
        $startPos = strpos($html, $start);
        if ($startPos === false) {
            return null;
        }
        $startPos += strlen($start);
        $endPos = strpos($html, $end, $startPos);
        if ($endPos === false) {
            return null;
        }

        return trim(substr($html, $startPos, $endPos - $startPos));
    }

    /**
     * ทำความสะอาด HTML - เอาแค่ตัวเลข
     */
    protected function cleanNumber(string $text): string
    {
        return preg_replace('/[^0-9]/', '', trim($text));
    }

    /**
     * Parse HTML and find elements by simple CSS-like selector
     * ใช้ DOMDocument + DOMXPath
     */
    protected function parseHtml(string $html): \DOMDocument
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        return $dom;
    }

    /**
     * XPath query on DOM
     */
    protected function xpath(\DOMDocument $dom, string $expression): \DOMNodeList
    {
        $xpath = new \DOMXPath($dom);

        return $xpath->query($expression);
    }

    /**
     * Get text content from first matching XPath node
     */
    protected function xpathText(\DOMDocument $dom, string $expression): ?string
    {
        $nodes = $this->xpath($dom, $expression);
        if ($nodes->length === 0) {
            return null;
        }

        return trim($nodes->item(0)->textContent);
    }

    /**
     * Health check - ทดสอบว่าเข้าถึง source URL ได้ไหม
     */
    public function healthCheck(ResultSource $source): bool
    {
        try {
            $url = $source->source_url;
            if (empty($url)) {
                return false;
            }
            $response = $this->httpGet($url, 10);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * วัดเวลา fetch
     */
    protected function measureTime(callable $callback): array
    {
        $start = microtime(true);
        $result = $callback();
        $elapsed = (int) ((microtime(true) - $start) * 1000);

        return [$result, $elapsed];
    }
}
