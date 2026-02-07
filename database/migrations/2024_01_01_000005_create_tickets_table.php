<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('lottery_round_id');
            $table->unsignedBigInteger('bet_type_id');
            $table->string('number', 10);
            $table->decimal('amount', 12, 2);
            $table->decimal('rate', 10, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->decimal('win_amount', 12, 2)->default(0);
            $table->timestamp('bet_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'bet_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
