<?php

namespace Farapayamak\Laravel\Drivers;

use Illuminate\Support\Facades\Log;

class LogDriver implements DriverInterface
{
    public function __construct(protected array $config) {}

    public function send(string|array $to, string $text, ?string $from = null, bool $isFlash = false): mixed
    {
        $recipients = is_array($to) ? implode(', ', $to) : $to;
        Log::info("[Farapayamak LogDriver] SMS sent to: [{$recipients}], text: [{$text}], flash: [{$isFlash}], from: [{$from}]");

        return [
            'Value'        => 'LOG_REC_' . time(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Success (Logged)',
        ];
    }

    public function sendPattern(int|string $bodyId, string $to, array|string $variables): mixed
    {
        $vars = is_array($variables) ? implode(';', $variables) : $variables;
        Log::info("[Farapayamak LogDriver] Pattern SMS sent to: [{$to}], bodyId: [{$bodyId}], vars: [{$vars}]");

        return [
            'Value'        => 'LOG_PATTERN_REC_' . time(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Success (Logged)',
        ];
    }

    public function sendOtp(string $to, string $text): mixed
    {
        Log::info("[Farapayamak LogDriver] OTP sent to: [{$to}], code/text: [{$text}]");

        return [
            'Value'        => 'LOG_OTP_REC_' . time(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Success (Logged)',
        ];
    }

    public function sendMultiple(array $to, array $messages, ?string $from = null): mixed
    {
        Log::info("[Farapayamak LogDriver] Multiple SMS sent to " . count($to) . " recipients");

        return [
            'Value'        => 'LOG_MULTIPLE_REC_' . time(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Success (Logged)',
        ];
    }

    public function schedule(string|array $to, string $text, string $scheduleDate, ?string $from = null): mixed
    {
        Log::info("[Farapayamak LogDriver] SMS scheduled for [{$scheduleDate}] to " . (is_array($to) ? count($to) : $to));

        return [
            'Value'        => 'LOG_SCHEDULE_REC_' . time(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Success (Logged)',
        ];
    }

    public function getCredit(): mixed
    {
        return 999999;
    }

    public function getCreditRial(): mixed
    {
        return 999999999;
    }

    public function getUserNumbers(): array
    {
        return [
            $this->config['from'] ?? '500012345678',
            '500099999999',
            '02188888888',
        ];
    }

    public function getDelivery(string|int $recId): mixed
    {
        return 1; // Delivered
    }

    public function getDeliveries(array $recIds): mixed
    {
        return array_fill_keys($recIds, 1);
    }

    public function getMessages(int $location = 1, ?string $from = null, int $index = 0, int $count = 100): mixed
    {
        return [];
    }

    public function getInboxCount(bool $isRead = false): int
    {
        return 0;
    }
}
