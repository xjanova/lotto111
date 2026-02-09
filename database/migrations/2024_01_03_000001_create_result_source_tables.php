<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('result_sources')) {
            Schema::create('result_sources', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lottery_type_id')->constrained('lottery_types')->cascadeOnDelete();
                $table->string('provider', 50);
                $table->string('name', 100);
                $table->enum('mode', ['auto', 'manual'])->default('auto');
                $table->string('source_url', 500)->nullable();
                $table->string('fallback_url', 500)->nullable();
                $table->json('scrape_config')->nullable();
                $table->json('schedule')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);
                $table->integer('retry_count')->default(3);
                $table->integer('retry_delay_seconds')->default(30);
                $table->integer('timeout_seconds')->default(30);
                $table->timestamp('last_fetched_at')->nullable();
                $table->string('last_status', 20)->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();

                $table->index(['lottery_type_id', 'is_active']);
                $table->index('provider');
            });
        }

        if (! Schema::hasTable('result_fetch_logs')) {
            Schema::create('result_fetch_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('result_source_id')->constrained('result_sources')->cascadeOnDelete();
                $table->foreignId('lottery_round_id')->nullable()->constrained('lottery_rounds')->nullOnDelete();
                $table->enum('status', ['success', 'failed', 'timeout', 'parse_error', 'no_data', 'skipped']);
                $table->string('source_url', 500)->nullable();
                $table->json('raw_response')->nullable();
                $table->json('parsed_results')->nullable();
                $table->text('error_message')->nullable();
                $table->integer('response_time_ms')->nullable();
                $table->integer('retry_attempt')->default(0);
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('fetched_at')->useCurrent();

                $table->index(['result_source_id', 'fetched_at']);
                $table->index('status');
                $table->index('fetched_at');
            });
        }

        if (! Schema::hasTable('yeekee_submissions')) {
            Schema::create('yeekee_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lottery_round_id')->constrained('lottery_rounds')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('number', 5);
                $table->integer('sequence')->default(0);
                $table->timestamp('created_at')->useCurrent();

                $table->index(['lottery_round_id', 'sequence']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('yeekee_submissions');
        Schema::dropIfExists('result_fetch_logs');
        Schema::dropIfExists('result_sources');
    }
};
