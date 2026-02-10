<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_gamifications')) {
            Schema::create('user_gamifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('xp')->default(0);
                $table->unsignedInteger('login_streak')->default(0);
                $table->unsignedInteger('longest_streak')->default(0);
                $table->date('last_daily_claim')->nullable();
                $table->unsignedInteger('spin_count')->default(0);
                $table->timestamps();

                $table->unique('user_id');
            });
        }

        if (! Schema::hasTable('missions')) {
            Schema::create('missions', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('type', 20)->default('daily');
                $table->string('condition_type', 50);
                $table->decimal('condition_value', 12, 2)->default(1);
                $table->unsignedInteger('reward_xp')->default(0);
                $table->decimal('reward_credit', 12, 2)->default(0);
                $table->unsignedInteger('reward_spins')->default(0);
                $table->unsignedBigInteger('reward_badge_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_missions')) {
            Schema::create('user_missions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
                $table->date('period_date');
                $table->decimal('progress', 12, 2)->default(0);
                $table->boolean('is_completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->boolean('is_claimed')->default(false);
                $table->timestamp('claimed_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'mission_id', 'period_date']);
            });
        }

        if (! Schema::hasTable('spin_rewards')) {
            Schema::create('spin_rewards', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type', 20);
                $table->decimal('value', 12, 2)->default(0);
                $table->decimal('probability', 8, 4)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_spin_histories')) {
            Schema::create('user_spin_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('spin_reward_id')->constrained()->cascadeOnDelete();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('condition_type', 50)->nullable();
                $table->decimal('condition_value', 12, 2)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['user_id', 'badge_id']);
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type', 50);
                $table->string('title');
                $table->text('body')->nullable();
                $table->text('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'read_at']);
            });
        }

        if (! Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('channels')->nullable();
                $table->boolean('draw_reminder')->default(true);
                $table->boolean('result_alert')->default(true);
                $table->boolean('jackpot_alert')->default(true);
                $table->boolean('hot_number_alert')->default(false);
                $table->boolean('friend_activity')->default(false);
                $table->boolean('promotion')->default(true);
                $table->string('quiet_start', 5)->nullable();
                $table->string('quiet_end', 5)->nullable();
                $table->unsignedInteger('reminder_minutes')->default(30);
                $table->timestamps();

                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('user_spin_histories');
        Schema::dropIfExists('spin_rewards');
        Schema::dropIfExists('user_missions');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('user_gamifications');
    }
};
