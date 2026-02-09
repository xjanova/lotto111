<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('deposits')) {
            Schema::create('deposits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->decimal('unique_amount', 12, 2)->nullable();
                $table->unsignedBigInteger('unique_amount_id')->nullable();
                $table->string('method', 30)->default('sms_auto');
                $table->string('status', 30);
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('matched_at')->nullable();
                $table->timestamp('credited_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->unsignedBigInteger('sms_notification_id')->nullable();
                $table->string('matched_bank')->nullable();
                $table->string('matched_reference')->nullable();
                $table->unsignedBigInteger('manual_matched_by')->nullable();
                $table->unsignedBigInteger('transaction_id')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'method', 'status']);
            });
        }

        if (! Schema::hasTable('sms_checker_devices')) {
            Schema::create('sms_checker_devices', function (Blueprint $table) {
                $table->id();
                $table->string('device_id')->unique();
                $table->string('name');
                $table->string('status', 20)->default('active');
                $table->timestamp('last_active_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('secret_key')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sms_payment_notifications')) {
            Schema::create('sms_payment_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('device_id');
                $table->string('bank', 50);
                $table->string('type', 20);
                $table->decimal('amount', 12, 2);
                $table->string('account_number')->nullable();
                $table->string('sender_or_receiver')->nullable();
                $table->string('reference_number')->nullable();
                $table->timestamp('sms_timestamp')->nullable();
                $table->string('nonce')->nullable();
                $table->string('status', 20)->default('pending');
                $table->unsignedBigInteger('matched_transaction_id')->nullable();
                $table->text('message')->nullable();
                $table->json('raw_payload')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('unique_payment_amounts')) {
            Schema::create('unique_payment_amounts', function (Blueprint $table) {
                $table->id();
                $table->decimal('base_amount', 12, 2);
                $table->decimal('unique_amount', 12, 4);
                $table->unsignedBigInteger('transaction_id');
                $table->string('transaction_type', 30);
                $table->string('status', 20)->default('reserved');
                $table->timestamp('expires_at')->useCurrent();
                $table->timestamp('matched_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sms_payment_nonces')) {
            Schema::create('sms_payment_nonces', function (Blueprint $table) {
                $table->id();
                $table->string('nonce')->unique();
                $table->string('device_id');
                $table->timestamp('used_at')->useCurrent();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_daily_stats')) {
            Schema::create('user_daily_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('stat_date');
                $table->decimal('deposit_amount', 14, 2)->default(0);
                $table->decimal('withdraw_amount', 14, 2)->default(0);
                $table->decimal('bet_amount', 14, 2)->default(0);
                $table->decimal('win_amount', 14, 2)->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'stat_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_daily_stats');
        Schema::dropIfExists('sms_payment_nonces');
        Schema::dropIfExists('unique_payment_amounts');
        Schema::dropIfExists('sms_payment_notifications');
        Schema::dropIfExists('sms_checker_devices');
        Schema::dropIfExists('deposits');
    }
};
