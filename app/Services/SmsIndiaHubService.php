<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsIndiaHubService
{
    public function sendOtp(string $phone, string $code, ?string $senderId = null, ?string $apiKey = null, ?string $brand = null): array
    {
        $apiKey = $apiKey ?? config('services.smsindiahub.api_key');
        $senderId = $senderId ?? config('services.smsindiahub.sender_id');
        $baseUrl = config('services.smsindiahub.base_url', 'https://cloud.smsindiahub.in/vendorsms/pushsms.aspx');
        $brand = $brand ?? config('app.name', 'Vegigo');

        $msg = "Welcome to the {$brand} powered by SMSINDIAHUB. Your OTP for registration is {$code}";

        $normalized = preg_replace('/\D+/', '', $phone);

        $query = [
            'APIKey' => $apiKey,
            'msisdn' => $normalized,
            'sid' => $senderId,
            'msg' => $msg,
            'fl' => 0,
            'dc' => 0,
            'gwid' => 2,
        ];

        $templateId = config('services.smsindiahub.template_id');
        if ($templateId) {
            $query['tid'] = $templateId;
        }

        $response = Http::get($baseUrl, $query);

        $body = $response->body();
        $parsed = json_decode($body, true);
        $ok = $response->successful() && (($parsed['ErrorCode'] ?? null) === '000' || str_contains($body, 'Success'));

        return [
            'success' => $ok,
            'raw' => $body,
            'parsed' => $parsed,
        ];
    }
}
