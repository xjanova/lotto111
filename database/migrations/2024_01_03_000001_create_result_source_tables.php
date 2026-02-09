<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ตาราง result_sources - แหล่งข้อมูลผลหวย (auto/manual)
        Schema::create('result_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_type_id')->constrained('lottery_types')->cascadeOnDelete();
            $table->string('provider', 50); // thai_gov, lao_mthai, hanoi_mthai, malaysia_gd, stock_set, yeekee_internal
            $table->string('name', 100);
            $table->enum('mode', ['auto', 'manual'])->default('auto'); // auto = ดึงจากเว็บอัตโนมัติ, manual = แอดมินกรอกเอง
            $table->string('source_url', 500)->nullable();
            $table->string('fallback_url', 500)->nullable();
            $table->json('scrape_config')->nullable(); // CSS selectors, API params, timing, etc.
            $table->json('schedule')->nullable(); // เวลาที่ต้อง fetch (cron-like)
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // ลำดับความสำคัญ (ถ้ามีหลาย source)
            $table->integer('retry_count')->default(3);
            $table->integer('retry_delay_seconds')->default(30);
            $table->integer('timeout_seconds')->default(30);
            $table->timestamp('last_fetched_at')->nullable();
            $table->string('last_status', 20)->nullable(); // success, failed, timeout
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['lottery_type_id', 'is_active']);
            $table->index('provider');
        });

        // ตาราง result_fetch_logs - บันทึกการ fetch ผล
        Schema::create('result_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_source_id')->constrained('result_sources')->cascadeOnDelete();
            $table->foreignId('lottery_round_id')->nullable()->constrained('lottery_rounds')->nullOnDelete();
            $table->enum('status', ['success', 'failed', 'timeout', 'parse_error', 'no_data', 'skipped']);
            $table->string('source_url', 500)->nullable();
            $table->json('raw_response')->nullable(); // เก็บ response ดิบ
            $table->json('parsed_results')->nullable(); // ผลที่ parse แล้ว
            $table->text('error_message')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->integer('retry_attempt')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('fetched_at')->useCurrent();

            $table->index(['result_source_id', 'fetched_at']);
            $table->index('status');
            $table->index('fetched_at');
        });

        // ตาราง yeekee_submissions - เลขที่ user ส่งมาคำนวณยี่กี
        Schema::create('yeekee_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_round_id')->constrained('lottery_rounds')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('number', 5); // เลข 5 หลักที่ user ส่ง
            $table->integer('sequence')->default(0); // ลำดับที่ส่ง
            $table->timestamp('created_at')->useCurrent();

            $table->index(['lottery_round_id', 'sequence']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yeekee_submissions');
        Schema::dropIfExists('result_fetch_logs');
        Schema::dropIfExists('result_sources');
    }
};
