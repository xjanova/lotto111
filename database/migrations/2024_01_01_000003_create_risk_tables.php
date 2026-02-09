<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_risk_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('risk_level', 20)->default('normal');
            $table->integer('risk_score')->default(50);
            $table->decimal('current_win_rate', 8, 2)->default(0);
            $table->decimal('win_rate_override', 8, 2)->nullable();
            $table->decimal('rate_adjustment_percent', 8, 2)->default(0);
            $table->boolean('is_auto_adjust')->default(true);
            $table->decimal('total_bet_amount', 14, 2)->default(0);
            $table->decimal('total_win_amount', 14, 2)->default(0);
            $table->decimal('total_deposit', 14, 2)->default(0);
            $table->integer('total_tickets')->default(0);
            $table->integer('total_wins')->default(0);
            $table->integer('consecutive_wins')->default(0);
            $table->integer('consecutive_losses')->default(0);
            $table->integer('bets_per_minute')->default(0);
            $table->decimal('today_bet_amount', 14, 2)->default(0);
            $table->decimal('today_win_amount', 14, 2)->default(0);
            $table->decimal('today_payout', 14, 2)->default(0);
            $table->integer('today_tickets')->default(0);
            $table->decimal('net_profit_for_system', 14, 2)->default(0);
            $table->text('blocked_numbers')->nullable();
            $table->decimal('max_bet_per_ticket', 12, 2)->nullable();
            $table->decimal('max_bet_per_number', 12, 2)->nullable();
            $table->decimal('max_payout_per_day', 14, 2)->nullable();
            $table->decimal('max_payout_per_ticket', 14, 2)->nullable();
            $table->timestamp('last_bet_at')->nullable();
            $table->foreignId('last_reviewed_by')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('risk_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('data_type', 30)->default('string');
            $table->text('description')->nullable();
            $table->string('group', 50)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('risk_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type', 50);
            $table->string('severity', 20);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('lottery_round_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('data')->nullable();
            $table->string('status', 20)->default('new');
            $table->foreignId('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();
        });

        Schema::create('rate_adjustment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('adjusted_by', 20);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('field_changed', 50);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->json('context_data')->nullable();
            $table->timestamps();
        });

        Schema::create('number_exposures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lottery_round_id');
            $table->unsignedBigInteger('bet_type_id');
            $table->string('number', 10);
            $table->decimal('total_bet_amount', 14, 2)->default(0);
            $table->integer('bet_count')->default(0);
            $table->decimal('potential_payout', 14, 2)->default(0);
            $table->decimal('effective_rate', 10, 2)->default(0);
            $table->decimal('rate_reduction_percent', 5, 2)->default(0);
            $table->string('risk_level', 20)->default('safe');
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            $table->unique(['lottery_round_id', 'bet_type_id', 'number'], 'number_exposure_unique');
        });

        Schema::create('profit_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('period_type', 20);
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->decimal('total_bet_amount', 14, 2)->default(0);
            $table->decimal('total_payout', 14, 2)->default(0);
            $table->decimal('total_deposit', 14, 2)->default(0);
            $table->decimal('total_withdraw', 14, 2)->default(0);
            $table->decimal('gross_profit', 14, 2)->default(0);
            $table->decimal('net_profit', 14, 2)->default(0);
            $table->decimal('margin_percent', 8, 2)->default(0);
            $table->integer('active_users')->default(0);
            $table->integer('new_users')->default(0);
            $table->integer('total_tickets')->default(0);
            $table->integer('total_wins')->default(0);
            $table->decimal('avg_win_rate', 8, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('system_realtime_stats', function (Blueprint $table) {
            $table->id();
            $table->string('stat_key')->unique();
            $table->decimal('stat_value', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_realtime_stats');
        Schema::dropIfExists('profit_snapshots');
        Schema::dropIfExists('number_exposures');
        Schema::dropIfExists('rate_adjustment_logs');
        Schema::dropIfExists('risk_alerts');
        Schema::dropIfExists('risk_settings');
        Schema::dropIfExists('user_risk_profiles');
    }
};
