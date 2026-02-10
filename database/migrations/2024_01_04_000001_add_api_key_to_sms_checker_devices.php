<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_checker_devices', function (Blueprint $table) {
            if (! Schema::hasColumn('sms_checker_devices', 'api_key')) {
                $table->string('api_key')->nullable()->unique()->after('secret_key');
            }
            if (! Schema::hasColumn('sms_checker_devices', 'platform')) {
                $table->string('platform', 50)->nullable()->after('api_key');
            }
            if (! Schema::hasColumn('sms_checker_devices', 'app_version')) {
                $table->string('app_version', 30)->nullable()->after('platform');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sms_checker_devices', function (Blueprint $table) {
            $table->dropColumn(['api_key', 'platform', 'app_version']);
        });
    }
};
