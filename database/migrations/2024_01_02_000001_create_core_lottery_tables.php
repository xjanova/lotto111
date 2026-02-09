<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('otp_verifications')) {
            Schema::create('otp_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('phone', 20);
                $table->string('otp_code', 6);
                $table->string('purpose', 30); // register, login, reset_password, verify
                $table->boolean('is_used')->default(false);
                $table->tinyInteger('attempts')->unsigned()->default(0);
                $table->timestamp('expires_at')->useCurrent();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['phone', 'purpose']);
                $table->index('expires_at');
            });
        }

        if (! Schema::hasTable('user_bank_accounts')) {
            Schema::create('user_bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('bank_code', 10);
                $table->string('bank_name', 100);
                $table->string('account_number', 20);
                $table->string('account_name', 100);
                $table->boolean('is_primary')->default(true);
                $table->timestamps();

                $table->index('user_id');
            });
        }

        if (! Schema::hasTable('lottery_types')) {
            Schema::create('lottery_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->string('category', 30); // government, yeekee, bank, international, set
                $table->string('country', 50)->nullable();
                $table->string('icon')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->index('category');
                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        if (! Schema::hasTable('lottery_rounds')) {
            Schema::create('lottery_rounds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lottery_type_id')->constrained('lottery_types');
                $table->string('round_code', 50)->unique();
                $table->integer('round_number')->nullable();
                $table->string('status', 20)->default('upcoming'); // upcoming, open, closed, resulted, cancelled
                $table->timestamp('open_at')->useCurrent();
                $table->timestamp('close_at')->useCurrent();
                $table->timestamp('result_at')->nullable();
                $table->timestamps();

                $table->index('lottery_type_id');
                $table->index('status');
                $table->index('close_at');
            });
        }

        if (! Schema::hasTable('lottery_results')) {
            Schema::create('lottery_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lottery_round_id')->constrained('lottery_rounds');
                $table->string('result_type', 30); // first_prize, three_top, three_tod, two_bottom, etc.
                $table->string('result_value', 10);
                $table->timestamp('created_at')->useCurrent();

                $table->index('lottery_round_id');
                $table->unique(['lottery_round_id', 'result_type']);
            });
        }

        if (! Schema::hasTable('bet_types')) {
            Schema::create('bet_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->string('slug', 50)->unique();
                $table->tinyInteger('digit_count');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bet_type_rates')) {
            Schema::create('bet_type_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lottery_type_id')->constrained('lottery_types');
                $table->foreignId('bet_type_id')->constrained('bet_types');
                $table->decimal('rate', 10, 2);
                $table->decimal('min_amount', 10, 2)->default(1.00);
                $table->decimal('max_amount', 10, 2)->default(99999.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['lottery_type_id', 'bet_type_id']);
            });
        }

        if (! Schema::hasTable('bet_limits')) {
            Schema::create('bet_limits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lottery_round_id')->constrained('lottery_rounds');
                $table->foreignId('bet_type_id')->constrained('bet_types');
                $table->string('number', 10);
                $table->decimal('max_amount', 10, 2)->default(0);
                $table->timestamp('created_at')->useCurrent();

                $table->index(['lottery_round_id', 'bet_type_id', 'number']);
            });
        }

        if (! Schema::hasTable('ticket_items')) {
            Schema::create('ticket_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
                $table->foreignId('bet_type_id')->constrained('bet_types');
                $table->string('number', 10);
                $table->decimal('amount', 10, 2);
                $table->decimal('rate', 10, 2);
                $table->decimal('win_amount', 12, 2)->default(0);
                $table->boolean('is_won')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('ticket_id');
                $table->index('number');
            });
        }

        // Add columns to tickets table if they don't exist yet
        if (Schema::hasTable('tickets') && ! Schema::hasColumn('tickets', 'ticket_code')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('ticket_code', 30)->unique()->after('id');
                $table->decimal('total_amount', 12, 2)->default(0)->after('amount');
                $table->decimal('total_win', 12, 2)->default(0)->after('total_amount');
                $table->timestamp('result_at')->nullable()->after('bet_at');
            });
        }

        if (! Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained();
                $table->foreignId('bank_account_id')->constrained('user_bank_accounts');
                $table->decimal('amount', 12, 2);
                $table->string('status', 20)->default('pending'); // pending, approved, rejected, processing, completed
                $table->text('note')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained();
                $table->string('type', 20); // deposit, withdraw, bet, win, refund, commission, adjustment
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_before', 12, 2);
                $table->decimal('balance_after', 12, 2);
                $table->string('reference_type', 50)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('description')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('user_id');
                $table->index('type');
                $table->index('created_at');
                $table->index(['reference_type', 'reference_id']);
            });
        }

        if (! Schema::hasTable('number_sets')) {
            Schema::create('number_sets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name', 100);
                $table->timestamps();

                $table->index('user_id');
            });
        }

        if (! Schema::hasTable('number_set_items')) {
            Schema::create('number_set_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('number_set_id')->constrained('number_sets')->cascadeOnDelete();
                $table->foreignId('bet_type_id')->constrained('bet_types');
                $table->string('number', 10);
                $table->decimal('amount', 10, 2)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('number_set_id');
            });
        }

        if (! Schema::hasTable('affiliate_commissions')) {
            Schema::create('affiliate_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained(); // receiver
                $table->foreignId('from_user_id')->constrained('users'); // bettor
                $table->foreignId('ticket_id')->nullable()->constrained('tickets');
                $table->decimal('bet_amount', 12, 2);
                $table->decimal('commission_rate', 5, 2);
                $table->decimal('commission', 12, 2);
                $table->string('status', 20)->default('pending'); // pending, paid, cancelled
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('user_id');
                $table->index('status');
                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users');
                $table->foreignId('receiver_id')->nullable()->constrained('users');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamp('created_at')->useCurrent();

                $table->index('receiver_id');
                $table->index('sender_id');
                $table->index('is_read');
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained();
                $table->string('type', 100);
                $table->string('title');
                $table->text('body')->nullable();
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'read_at']);
            });
        }

        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('group', 50)->default('general');
                $table->string('type', 20)->default('string'); // string, integer, boolean, json, text
                $table->timestamps();

                $table->index('group');
            });
        }

        if (! Schema::hasTable('admin_logs')) {
            Schema::create('admin_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained('users');
                $table->string('action', 100);
                $table->text('description')->nullable();
                $table->string('target_type', 50)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('admin_id');
                $table->index('action');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('number_set_items');
        Schema::dropIfExists('number_sets');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('withdrawals');

        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'ticket_code')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn(['ticket_code', 'total_amount', 'total_win', 'result_at']);
            });
        }

        Schema::dropIfExists('ticket_items');
        Schema::dropIfExists('bet_limits');
        Schema::dropIfExists('bet_type_rates');
        Schema::dropIfExists('bet_types');
        Schema::dropIfExists('lottery_results');
        Schema::dropIfExists('lottery_rounds');
        Schema::dropIfExists('lottery_types');
        Schema::dropIfExists('user_bank_accounts');
        Schema::dropIfExists('otp_verifications');
    }
};
