<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Send notification through preferred channels
     */
    public function send(User $user, string $type, string $title, string $body, array $data = []): void
    {
        $preferences = $user->notificationPreference;

        if (!$preferences || !$this->shouldSend($preferences, $type)) {
            return;
        }

        // Check quiet hours
        if ($this->isQuietHours($preferences)) {
            return;
        }

        $channels = json_decode($preferences->channels, true) ?? ['app'];

        foreach ($channels as $channel) {
            match ($channel) {
                'app' => $this->sendInApp($user, $type, $title, $body, $data),
                'line' => $this->sendLine($user, $title, $body),
                'push' => $this->sendPush($user, $title, $body, $data),
                'sms' => $this->sendSms($user, $body),
                default => null,
            };
        }
    }

    /**
     * Send draw reminder notifications
     */
    public function sendDrawReminders(): void
    {
        $preferences = NotificationPreference::where('draw_reminder', true)->get();

        foreach ($preferences as $pref) {
            $minutesBefore = $pref->reminder_minutes ?? 30;
            // Logic to check upcoming draws and send reminders
        }
    }

    /**
     * Send result announcement
     */
    public function sendResultAlert(User $user, string $lotteryName, array $results, bool $hasWon, float $winAmount = 0): void
    {
        $emoji = $hasWon ? '🎉' : '📊';
        $title = "{$emoji} ผลหวย {$lotteryName} ออกแล้ว!";
        $body = $hasWon
            ? "ยินดีด้วย! คุณถูกรางวัล {$winAmount} บาท"
            : "ผลรางวัล: " . implode(', ', array_map(fn($r) => "{$r['type']}: {$r['value']}", $results));

        $this->send($user, 'result_alert', $title, $body, [
            'type' => 'result',
            'lottery' => $lotteryName,
            'won' => $hasWon,
            'amount' => $winAmount,
        ]);
    }

    /**
     * Send hot number alert
     */
    public function sendHotNumberAlert(User $user, string $lotteryName, array $numbers): void
    {
        $title = "🔥 เลขเด็ด AI - {$lotteryName}";
        $body = "AI พบเลขน่าสนใจ: " . implode(', ', $numbers);

        $this->send($user, 'hot_number_alert', $title, $body, [
            'type' => 'hot_number',
            'numbers' => $numbers,
        ]);
    }

    private function sendInApp(User $user, string $type, string $title, string $body, array $data): void
    {
        Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => json_encode($data),
        ]);

        // Broadcast via WebSocket
        broadcast(new \App\Events\NotificationSent($user, $title, $body, $data));
    }

    private function sendLine(User $user, string $title, string $body): void
    {
        if (!$user->line_user_id) return;

        $lineToken = config('services.line.channel_access_token');

        Http::withToken($lineToken)->post('https://api.line.me/v2/bot/message/push', [
            'to' => $user->line_user_id,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => "{$title}\n{$body}",
                ],
            ],
        ]);
    }

    private function sendPush(User $user, string $title, string $body, array $data): void
    {
        // Web Push Notification via PWA Service Worker
        // Implementation depends on web-push library
    }

    private function sendSms(User $user, string $body): void
    {
        app(SmsService::class)->send($user->phone, $body);
    }

    private function shouldSend(NotificationPreference $pref, string $type): bool
    {
        return match ($type) {
            'draw_reminder' => $pref->draw_reminder,
            'result_alert' => $pref->result_alert,
            'jackpot_alert' => $pref->jackpot_alert,
            'hot_number_alert' => $pref->hot_number_alert,
            'friend_activity' => $pref->friend_activity,
            'promotion' => $pref->promotion,
            default => true,
        };
    }

    private function isQuietHours(NotificationPreference $pref): bool
    {
        if (!$pref->quiet_start || !$pref->quiet_end) return false;

        $now = now()->format('H:i');
        return $now >= $pref->quiet_start && $now <= $pref->quiet_end;
    }
}
