<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function sendAs(string $agentName, string $chatId, string $text): ?string
    {
        $token = config("telegram.bots.{$agentName}");
        if (!$token) {
            Log::warning("TelegramService: no bot token configured for agent", [
                'agent' => $agentName,
            ]);
            return null;
        }

        if (!$chatId) {
            return null;
        }

        $url = rtrim(config('telegram.api_base'), '/') . "/bot{$token}/sendMessage";
        $timeout = (int) config('telegram.timeout_seconds', 10);

        try {
            $response = Http::timeout($timeout)->post($url, [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

            if (!$response->successful()) {
                Log::warning("TelegramService: send failed", [
                    'agent' => $agentName,
                    'chat_id' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return (string) data_get($response->json(), 'result.message_id');
        } catch (\Throwable $e) {
            Log::warning("TelegramService: exception sending message", [
                'agent' => $agentName,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
