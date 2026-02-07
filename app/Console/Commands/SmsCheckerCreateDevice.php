<?php

namespace App\Console\Commands;

use App\Models\SmsCheckerDevice;
use Illuminate\Console\Command;

/**
 * Command: สร้างอุปกรณ์ SMS Checker ใหม่
 *
 * Usage: php artisan smschecker:create-device "Device Name"
 */
class SmsCheckerCreateDevice extends Command
{
    protected $signature = 'smschecker:create-device {name : Device name}';
    protected $description = 'Create a new SMS Checker device with API key';

    public function handle(): int
    {
        $name = $this->argument('name');

        $device = SmsCheckerDevice::create([
            'device_id' => 'DEV-' . strtoupper(bin2hex(random_bytes(6))),
            'device_name' => $name,
            'api_key' => SmsCheckerDevice::generateApiKey(),
            'secret_key' => SmsCheckerDevice::generateSecretKey(),
            'status' => 'active',
        ]);

        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║    SMS Checker Device Created Successfully    ║');
        $this->info('╠══════════════════════════════════════════════╣');
        $this->info("║  Device ID:   {$device->device_id}");
        $this->info("║  Name:        {$device->device_name}");
        $this->info("║  API Key:     {$device->api_key}");
        $this->info("║  Secret Key:  {$device->secret_key}");
        $this->info('╠══════════════════════════════════════════════╣');
        $this->info('║  QR Config URL:');
        $this->info("║  " . config('app.url') . "/admin/sms-deposit/devices/{$device->id}/qr");
        $this->info('╚══════════════════════════════════════════════╝');

        $this->newLine();
        $this->warn('⚠️  เก็บ API Key และ Secret Key ไว้อย่างปลอดภัย');
        $this->warn('   ใช้ QR Code ที่หน้า Admin เพื่อ setup อุปกรณ์ Android');

        return self::SUCCESS;
    }
}
