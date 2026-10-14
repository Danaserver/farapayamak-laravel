<?php

namespace Farapayamak\Laravel\Drivers;

use Farapayamak\Laravel\Exceptions\FarapayamakException;
use Farapayamak\Laravel\Support\PhoneNumber;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Throwable;

class RestDriver implements DriverInterface
{
    protected GuzzleClient $client;
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected ?string $defaultFrom;
    protected int $timeout;

    public function __construct(protected array $config)
    {
        $this->baseUrl     = rtrim($config['rest']['base_url'] ?? 'https://rest.payamak-panel.com/api/SendSMS/', '/') . '/';
        $this->username    = (string) ($config['username'] ?? '');
        $this->password    = (string) ($config['password'] ?? '');
        $this->defaultFrom = !empty($config['from']) ? (string) $config['from'] : null;
        $this->timeout     = (int) ($config['timeout'] ?? 30);

        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout'  => $this->timeout,
            'headers'  => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept'       => 'application/json',
            ],
        ]);
    }

    /**
     * Send simple SMS.
     */
    public function send(string|array $to, string $text, ?string $from = null, bool $isFlash = false): mixed
    {
        $fromNumber = $from ?: $this->defaultFrom;

        if (is_array($to)) {
            $cleaned = PhoneNumber::cleanMultiple($to);
            // If multiple numbers, use SendSMS with comma or loop depending on size
            $toParam = implode(',', $cleaned);
        } else {
            $toParam = PhoneNumber::clean($to);
        }

        return $this->post('SendSMS', [
            'from'    => $fromNumber,
            'to'      => $toParam,
            'text'    => $text,
            'isFlash' => $isFlash,
        ]);
    }

    /**
     * Send service message using an approved template (BaseServiceNumber).
     */
    public function sendPattern(int|string $bodyId, string $to, array|string $variables): mixed
    {
        $cleanedTo = PhoneNumber::clean($to);

        // Format variables: if array, join with ';'
        if (is_array($variables)) {
            $text = implode(';', array_map('strval', $variables));
        } else {
            $text = (string) $variables;
        }

        return $this->post('BaseServiceNumber', [
            'to'      => $cleanedTo,
            'bodyId'  => (int) $bodyId,
            'text'    => $text,
        ]);
    }

    /**
     * Schedule a service message using BaseServiceNumberSchedule.
     */
    public function schedulePattern(int|string $bodyId, string $to, array|string $variables, string $scheduleDate): mixed
    {
        $cleanedTo = PhoneNumber::clean($to);

        if (is_array($variables)) {
            $text = implode(';', array_map('strval', $variables));
        } else {
            $text = (string) $variables;
        }

        return $this->post('BaseServiceNumberSchedule', [
            'to'           => $cleanedTo,
            'bodyId'       => (int) $bodyId,
            'text'         => $text,
            'scheduleDate' => $scheduleDate,
        ]);
    }

    /**
     * Send OTP.
     */
    public function sendOtp(string $to, string $text): mixed
    {
        $cleanedTo = PhoneNumber::clean($to);

        return $this->post('SendOTP', [
            'to'   => $cleanedTo,
            'text' => $text,
        ]);
    }

    /**
     * Send multiple SMS (Peer-to-Peer).
     */
    public function sendMultiple(array $to, array $messages, ?string $from = null): mixed
    {
        $fromNumber = $from ?: $this->defaultFrom;
        $cleanedTo = PhoneNumber::cleanMultiple($to);

        return $this->post('SendMultipleSMS', [
            'from'    => $fromNumber,
            'to'      => $cleanedTo,
            'text'    => array_values($messages),
            'isFlash' => false,
        ]);
    }

    /**
     * Schedule SMS.
     */
    public function schedule(string|array $to, string $text, string $scheduleDate, ?string $from = null): mixed
    {
        $fromNumber = $from ?: $this->defaultFrom;
        $toParam = is_array($to) ? implode(',', PhoneNumber::cleanMultiple($to)) : PhoneNumber::clean($to);

        return $this->post('ScheduleSMS', [
            'from'         => $fromNumber,
            'to'           => $toParam,
            'text'         => $text,
            'scheduleDate' => $scheduleDate,
            'isFlash'      => false,
        ]);
    }

    /**
     * Get remaining credit (count).
     */
    public function getCredit(): mixed
    {
        $res = $this->post('GetCredit', []);
        return $res['Value'] ?? $res;
    }

    /**
     * Get remaining credit in Rials.
     */
    public function getCreditRial(): mixed
    {
        $res = $this->post('GetCreditRial', []);
        return $res['Value'] ?? $res;
    }

    /**
     * Get list of sender numbers assigned to the account.
     */
    public function getUserNumbers(): array
    {
        $res = $this->post('GetUserNumbers', []);

        if (isset($res['Value'])) {
            if (is_array($res['Value'])) {
                return $res['Value'];
            }
            if (is_string($res['Value'])) {
                $decoded = json_decode($res['Value'], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
                return array_filter(explode(',', $res['Value']));
            }
        }

        return is_array($res) ? $res : [];
    }

    /**
     * Get delivery status.
     */
    public function getDelivery(string|int $recId): mixed
    {
        $res = $this->post('GetDelivery', [
            'recId' => (string) $recId,
        ]);

        return $res['Value'] ?? $res;
    }

    /**
     * Get multiple deliveries.
     */
    public function getDeliveries(array $recIds): mixed
    {
        return $this->post('GetDeliveries', [
            'recIds' => array_values($recIds),
        ]);
    }

    /**
     * Get received messages.
     */
    public function getMessages(int $location = 1, ?string $from = null, int $index = 0, int $count = 100): mixed
    {
        return $this->post('GetMessages', [
            'location' => $location,
            'from'     => $from ?: ($this->defaultFrom ?? ''),
            'index'    => $index,
            'count'    => $count,
        ]);
    }

    /**
     * Get inbox count.
     */
    public function getInboxCount(bool $isRead = false): int
    {
        $res = $this->post('GetInboxCount', [
            'isRead' => $isRead,
        ]);

        return isset($res['Value']) ? (int) $res['Value'] : (int) ($res ?? 0);
    }

    /**
     * Calculate SMS price before sending.
     */
    public function calculatePrice(string $text, ?string $from = null, int $count = 1): mixed
    {
        return $this->post('CalculateSmsPrice', [
            'from'  => $from ?: ($this->defaultFrom ?? ''),
            'text'  => $text,
            'count' => $count,
        ]);
    }

    /**
     * Smart send message.
     */
    public function smartSend(string $to, string $text, ?string $from = null): mixed
    {
        return $this->post('SmartSend', [
            'from' => $from ?: ($this->defaultFrom ?? ''),
            'to'   => PhoneNumber::clean($to),
            'text' => $text,
        ]);
    }

    /**
     * Perform HTTP POST request to Farapayamak REST API.
     *
     * @throws FarapayamakException
     */
    protected function post(string $endpoint, array $data): array
    {
        $payload = array_merge([
            'username' => $this->username,
            'password' => $this->password,
        ], $data);

        try {
            $response = $this->client->post($endpoint, [
                'json' => $payload,
            ]);

            $contents = $response->getBody()->getContents();
            $result = json_decode($contents, true);

            if (!is_array($result)) {
                // Some endpoints return direct scalar value or string
                if (is_numeric($contents) && (int) $contents < 0) {
                    throw FarapayamakException::fromCode($contents);
                }
                return ['Value' => $contents, 'RetStatus' => 1];
            }

            // Check RetStatus or error codes in Value
            if (isset($result['RetStatus']) && (int) $result['RetStatus'] !== 1) {
                $errCode = $result['Value'] ?? $result['RetStatus'];
                $msg = $result['StrRetStatus'] ?? null;
                throw FarapayamakException::fromCode($errCode, $msg);
            }

            // Check if Value contains a known error code (e.g. negative numbers)
            if (isset($result['Value']) && is_numeric($result['Value'])) {
                $numVal = (int) $result['Value'];
                if ($numVal < 0 || ($numVal >= 2 && $numVal <= 19)) {
                    throw FarapayamakException::fromCode($numVal, $result['StrRetStatus'] ?? null);
                }
            }

            return $result;
        } catch (GuzzleException $e) {
            throw new FarapayamakException("خطای ارتباط با سرور REST فراپیامک: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
