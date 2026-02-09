<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultFetchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'result_source_id',
        'lottery_round_id',
        'status',
        'source_url',
        'raw_response',
        'parsed_results',
        'error_message',
        'response_time_ms',
        'retry_attempt',
        'ip_address',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_response' => 'array',
            'parsed_results' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    public function resultSource(): BelongsTo
    {
        return $this->belongsTo(ResultSource::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(LotteryRound::class, 'lottery_round_id');
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public static function logFetch(
        int $sourceId,
        string $status,
        ?string $url = null,
        ?array $rawResponse = null,
        ?array $parsedResults = null,
        ?string $error = null,
        ?int $responseTimeMs = null,
        int $retryAttempt = 0,
        ?int $roundId = null,
    ): static {
        return static::create([
            'result_source_id' => $sourceId,
            'lottery_round_id' => $roundId,
            'status' => $status,
            'source_url' => $url,
            'raw_response' => $rawResponse,
            'parsed_results' => $parsedResults,
            'error_message' => $error,
            'response_time_ms' => $responseTimeMs,
            'retry_attempt' => $retryAttempt,
            'ip_address' => request()?->ip(),
            'fetched_at' => now(),
        ]);
    }
}
