<?php

namespace Farapayamak\Laravel\Testing;

use Farapayamak\Laravel\Drivers\DriverInterface;
use Farapayamak\Laravel\Support\PhoneNumber;
use PHPUnit\Framework\Assert as PHPUnit;

class FarapayamakFake implements DriverInterface
{
    /**
     * Recorded messages.
     */
    protected array $records = [];

    public function send(string|array $to, string $text, ?string $from = null, bool $isFlash = false): mixed
    {
        $recipients = is_array($to) ? PhoneNumber::cleanMultiple($to) : [PhoneNumber::clean($to)];

        $record = [
            'type'      => 'send',
            'to'        => $recipients,
            'text'      => $text,
            'from'      => $from,
            'isFlash'   => $isFlash,
            'timestamp' => microtime(true),
        ];

        $this->records[] = $record;

        return [
            'Value'        => 'FAKE_REC_' . uniqid(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Fake Success',
        ];
    }

    public function sendPattern(int|string $bodyId, string $to, array|string $variables): mixed
    {
        $record = [
            'type'      => 'pattern',
            'bodyId'    => $bodyId,
            'to'        => [PhoneNumber::clean($to)],
            'variables' => is_array($variables) ? $variables : [$variables],
            'timestamp' => microtime(true),
        ];

        $this->records[] = $record;

        return [
            'Value'        => 'FAKE_PATTERN_' . uniqid(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Fake Success',
        ];
    }

    public function sendOtp(string $to, string $text): mixed
    {
        $record = [
            'type'      => 'otp',
            'to'        => [PhoneNumber::clean($to)],
            'text'      => $text,
            'timestamp' => microtime(true),
        ];

        $this->records[] = $record;

        return [
            'Value'        => 'FAKE_OTP_' . uniqid(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Fake Success',
        ];
    }

    public function sendMultiple(array $to, array $messages, ?string $from = null): mixed
    {
        $record = [
            'type'      => 'multiple',
            'to'        => PhoneNumber::cleanMultiple($to),
            'messages'  => $messages,
            'from'      => $from,
            'timestamp' => microtime(true),
        ];

        $this->records[] = $record;

        return [
            'Value'        => 'FAKE_MULTIPLE_' . uniqid(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Fake Success',
        ];
    }

    public function schedule(string|array $to, string $text, string $scheduleDate, ?string $from = null): mixed
    {
        $record = [
            'type'         => 'schedule',
            'to'           => is_array($to) ? PhoneNumber::cleanMultiple($to) : [PhoneNumber::clean($to)],
            'text'         => $text,
            'scheduleDate' => $scheduleDate,
            'from'         => $from,
            'timestamp'    => microtime(true),
        ];

        $this->records[] = $record;

        return [
            'Value'        => 'FAKE_SCHEDULE_' . uniqid(),
            'RetStatus'    => 1,
            'StrRetStatus' => 'Fake Success',
        ];
    }

    public function getCredit(): mixed
    {
        return 100000;
    }

    public function getCreditRial(): mixed
    {
        return 1000000000;
    }

    public function getUserNumbers(): array
    {
        return ['500012345678', '02188888888'];
    }

    public function getDelivery(string|int $recId): mixed
    {
        return 1;
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

    // -------------------------------------------------------------
    // Assertion Methods
    // -------------------------------------------------------------

    /**
     * Assert that a message matching the criteria was sent.
     */
    public function assertSent(callable $callback): void
    {
        $passed = false;
        foreach ($this->records as $record) {
            if ($callback($record)) {
                $passed = true;
                break;
            }
        }

        PHPUnit::assertTrue($passed, "The expected SMS was not sent.");
    }

    /**
     * Assert that a specific pattern was sent to a specific number.
     */
    public function assertPatternSent(int|string $bodyId, ?string $to = null, ?array $variables = null): void
    {
        $cleanedTo = $to ? PhoneNumber::clean($to) : null;
        $found = false;

        foreach ($this->records as $record) {
            if ($record['type'] === 'pattern' && (string) $record['bodyId'] === (string) $bodyId) {
                if ($cleanedTo !== null && !in_array($cleanedTo, $record['to'], true)) {
                    continue;
                }
                if ($variables !== null && $record['variables'] !== $variables) {
                    continue;
                }
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue(
            $found,
            "The pattern [{$bodyId}] was not sent" . ($cleanedTo ? " to [{$cleanedTo}]" : "") . "."
        );
    }

    /**
     * Assert that no SMS was sent.
     */
    public function assertNothingSent(): void
    {
        PHPUnit::assertEmpty($this->records, "Expected no SMS to be sent, but " . count($this->records) . " messages were sent.");
    }

    /**
     * Assert the total number of sent SMS messages.
     */
    public function assertSentCount(int $expectedCount): void
    {
        PHPUnit::assertCount($expectedCount, $this->records);
    }

    /**
     * Get all recorded dispatches.
     */
    public function recorded(): array
    {
        return $this->records;
    }
}
