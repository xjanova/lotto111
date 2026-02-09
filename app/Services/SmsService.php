<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): bool
    {
        $provider = config('sms.provider', 'thaibulksms');

        return match ($provider) {
            'thaibulksms' => $this->sendViaThaiBulkSms($phone, $message),
            'thaisms' => $this->sendViaThaiSms($phone, $message),
            default => $this->sendViaThaiBulkSms($phone, $message),
        };
    }

    private function sendViaThaiBulkSms(string $phone, string $message): bool
    {
        $config = config('sms.thaibulksms');

        if (empty($config['api_key']) || empty($config['api_secret'])) {
            Log::warning('ThaiBulkSMS credentials not configured, SMS not sent', [
                'phone' => substr($phone, 0, 6) . '****',
            ]);
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post($config['base_url'], [
                    'key' => $config['api_key'],
                    'secret' => $config['api_secret'],
                    'msisdn' => $this->formatPhone($phone),
                    'message' => $message,
                    'sender' => $config['sender'],
                ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'phone' => substr($phone, 0, 6) . '****',
                    'provider' => 'thaibulksms',
                ]);
                return true;
            }

            Log::error('SMS send failed', [
                'phone' => substr($phone, 0, 6) . '****',
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('SMS send exception', [
                'phone' => substr($phone, 0, 6) . '****',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function sendViaThaiSms(string $phone, string $message): bool
    {
        $config = config('sms.thaisms');

        if (empty($config['api_key'])) {
            Log::warning('ThaiSMS credentials not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
            ])->timeout(30)->post($config['base_url'], [
                'to' => $this->formatPhone($phone),
                'message' => $message,
                'sender' => $config['sender'],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('ThaiSMS send exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '66' . substr($phone, 1);
        }

        return $phone;
    }
}
