<?php

namespace Farapayamak\Laravel\Drivers;

interface DriverInterface
{
    /**
     * Send simple or flash SMS to one or multiple recipients.
     */
    public function send(string|array $to, string $text, ?string $from = null, bool $isFlash = false): mixed;

    /**
     * Send an approved service template message (BaseServiceNumber / Pattern) with variables.
     *
     * @param int|string $bodyId The approved template ID from panel
     * @param string $to Recipient phone number
     * @param array<string|int>|string $variables Variable values to substitute into the template
     */
    public function sendPattern(int|string $bodyId, string $to, array|string $variables): mixed;

    /**
     * Send OTP verification code.
     */
    public function sendOtp(string $to, string $text): mixed;

    /**
     * Send different messages to different recipients (Peer to Peer).
     *
     * @param array<string> $to
     * @param array<string> $messages
     */
    public function sendMultiple(array $to, array $messages, ?string $from = null): mixed;

    /**
     * Schedule an SMS for future delivery.
     */
    public function schedule(string|array $to, string $text, string $scheduleDate, ?string $from = null): mixed;

    /**
     * Get remaining SMS credit count.
     */
    public function getCredit(): mixed;

    /**
     * Get remaining credit in Rials.
     */
    public function getCreditRial(): mixed;

    /**
     * Get the list of registered sender lines belonging to the account.
     *
     * @return array<string>
     */
    public function getUserNumbers(): array;

    /**
     * Get delivery status for a single record ID.
     */
    public function getDelivery(string|int $recId): mixed;

    /**
     * Get delivery statuses for multiple record IDs.
     */
    public function getDeliveries(array $recIds): mixed;

    /**
     * Get received messages from the panel inbox.
     */
    public function getMessages(int $location = 1, ?string $from = null, int $index = 0, int $count = 100): mixed;

    /**
     * Get total count of messages in the inbox.
     */
    public function getInboxCount(bool $isRead = false): int;
}
