<?php

namespace Farapayamak\Laravel\Drivers;

use Farapayamak\Laravel\Exceptions\FarapayamakException;
use Farapayamak\Laravel\Support\PhoneNumber;
use SoapClient;
use Throwable;

class SoapDriver implements DriverInterface
{
    protected string $username;
    protected string $password;
    protected ?string $defaultFrom;
    protected array $wsdlUrls;
    protected array $clients = [];

    public function __construct(protected array $config)
    {
        $this->username    = (string) ($config['username'] ?? '');
        $this->password    = (string) ($config['password'] ?? '');
        $this->defaultFrom = !empty($config['from']) ? (string) $config['from'] : null;
        $this->wsdlUrls    = $config['soap'] ?? [];
    }

    /**
     * Get or instantiate a SoapClient for a specific service family.
     */
    protected function getSoapClient(string $service = 'send'): SoapClient
    {
        if (!isset($this->clients[$service])) {
            $url = $this->wsdlUrls[$service] ?? 'http://api.payamak-panel.com/post/send.asmx?wsdl';
            $this->clients[$service] = new SoapClient($url, [
                'trace'      => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_BOTH,
                'connection_timeout' => $this->config['timeout'] ?? 30,
            ]);
        }

        return $this->clients[$service];
    }

    /**
     * Send simple SMS via SOAP.
     */
    public function send(string|array $to, string $text, ?string $from = null, bool $isFlash = false): mixed
    {
        $fromNumber = $from ?: $this->defaultFrom;
        $client = $this->getSoapClient('send');

        $recipients = is_array($to) ? PhoneNumber::cleanMultiple($to) : [PhoneNumber::clean($to)];

        try {
            $params = [
                'username' => $this->username,
                'password' => $this->password,
                'from'     => $fromNumber,
                'to'       => $recipients,
                'text'     => $text,
                'isflash'  => $isFlash,
                'udh'      => '',
                'recId'    => [0],
                'status'   => [0],
            ];

            $response = $client->SendSimpleSMS2($params);
            return $response->SendSimpleSMS2Result ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای وب‌سرویس SOAP ارسال پیامک: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Send pattern SMS via SOAP (SendByBaseNumber2).
     */
    public function sendPattern(int|string $bodyId, string $to, array|string $variables): mixed
    {
        $cleanedTo = PhoneNumber::clean($to);
        $client = $this->getSoapClient('send');

        if (is_array($variables)) {
            $text = implode(';', array_map('strval', $variables));
        } else {
            $text = (string) $variables;
        }

        try {
            $params = [
                'username' => $this->username,
                'password' => $this->password,
                'text'     => $text,
                'to'       => $cleanedTo,
                'bodyId'   => (int) $bodyId,
            ];

            $response = $client->SendByBaseNumber2($params);
            $result = $response->SendByBaseNumber2Result ?? $response;

            if (is_numeric($result)) {
                $numVal = (int) $result;
                if ($numVal < 0 || ($numVal >= 2 && $numVal <= 19)) {
                    throw FarapayamakException::fromCode($numVal);
                }
            }

            return $result;
        } catch (Throwable $e) {
            if ($e instanceof FarapayamakException) {
                throw $e;
            }
            throw new FarapayamakException("خطای SOAP ارسال بر اساس الگو (پترن): " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Send OTP via SOAP fallback.
     */
    public function sendOtp(string $to, string $text): mixed
    {
        return $this->send($to, $text);
    }

    /**
     * Send multiple SMS via SOAP.
     */
    public function sendMultiple(array $to, array $messages, ?string $from = null): mixed
    {
        $fromNumber = $from ?: $this->defaultFrom;
        $client = $this->getSoapClient('send');

        try {
            $params = [
                'username' => $this->username,
                'password' => $this->password,
                'from'     => $fromNumber,
                'to'       => PhoneNumber::cleanMultiple($to),
                'text'     => array_values($messages),
                'isflash'  => false,
                'udh'      => '',
                'recId'    => [0],
                'status'   => [0],
            ];

            $response = $client->SendSms($params);
            return $response->SendSmsResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای SOAP ارسال متناظر: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Schedule SMS via SOAP.
     */
    public function schedule(string|array $to, string $text, string $scheduleDate, ?string $from = null): mixed
    {
        $fromNumber = $from ?: $this->defaultFrom;
        $client = $this->getSoapClient('schedule');

        try {
            $params = [
                'username'     => $this->username,
                'password'     => $this->password,
                'to'           => is_array($to) ? PhoneNumber::cleanMultiple($to) : [PhoneNumber::clean($to)],
                'from'         => $fromNumber,
                'text'         => $text,
                'isflash'      => false,
                'scheduleDateTime' => $scheduleDate,
                'period'       => 1,
            ];

            $response = $client->AddSchedule($params);
            return $response->AddScheduleResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای SOAP زمان‌بندی پیامک: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Add recurring/usance schedule (ارسال‌های دوره‌ای و اقساطی).
     */
    public function addUsance(string|array $to, string $text, string $scheduleDateTime, int $count, int $type): mixed
    {
        $client = $this->getSoapClient('schedule');

        try {
            $params = [
                'username'         => $this->username,
                'password'         => $this->password,
                'to'               => is_array($to) ? PhoneNumber::cleanMultiple($to) : [PhoneNumber::clean($to)],
                'from'             => $this->defaultFrom,
                'text'             => $text,
                'isflash'          => false,
                'scheduleDateTime' => $scheduleDateTime,
                'count'            => $count,
                'type'             => $type,
            ];

            $response = $client->AddUsance($params);
            return $response->AddUsanceResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای SOAP ارسال دوره‌ای: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Send Voice SMS (Text-To-Speech) with optional SMS fallback.
     */
    public function sendVoice(string $to, string $speechText, ?string $smsText = null): mixed
    {
        $client = $this->getSoapClient('voice');
        $cleanedTo = PhoneNumber::clean($to);

        try {
            if ($smsText) {
                $params = [
                    'username'   => $this->username,
                    'password'   => $this->password,
                    'from'       => $this->defaultFrom,
                    'to'         => $cleanedTo,
                    'smsText'    => $smsText,
                    'speechText' => $speechText,
                ];
                $response = $client->SendSMSWithSpeechText($params);
                return $response->SendSMSWithSpeechTextResult ?? $response;
            }

            $params = [
                'username'   => $this->username,
                'password'   => $this->password,
                'to'         => $cleanedTo,
                'speechText' => $speechText,
            ];
            $response = $client->SendSpeechText($params);
            return $response->SendSpeechTextResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای SOAP پیامک صوتی: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Contacts: Add a contact group.
     */
    public function addContactGroup(string $groupName, string $descriptions = ''): mixed
    {
        $client = $this->getSoapClient('contacts');

        try {
            $response = $client->AddGroup([
                'username'     => $this->username,
                'password'     => $this->password,
                'groupName'    => $groupName,
                'descriptions' => $descriptions,
            ]);
            return $response->AddGroupResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای ایجاد گروه مخاطبین: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Contacts: Add a single contact.
     */
    public function addContact(int $groupId, string $mobile, string $firstName = '', string $lastName = '', string $email = ''): mixed
    {
        $client = $this->getSoapClient('contacts');

        try {
            $response = $client->AddContact([
                'username'  => $this->username,
                'password'  => $this->password,
                'options'   => [
                    'GroupId'   => $groupId,
                    'Mobile'    => PhoneNumber::clean($mobile),
                    'FirstName' => $firstName,
                    'LastName'  => $lastName,
                    'Email'     => $email,
                ],
            ]);
            return $response->AddContactResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای افزودن مخاطب: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getCredit(): mixed
    {
        $client = $this->getSoapClient('send');

        try {
            $response = $client->GetCredit([
                'username' => $this->username,
                'password' => $this->password,
            ]);
            return $response->GetCreditResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای دریافت اعتبار SOAP: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getCreditRial(): mixed
    {
        $client = $this->getSoapClient('users');

        try {
            $response = $client->GetOwnCreditRial([
                'username' => $this->username,
                'password' => $this->password,
            ]);
            return $response->GetOwnCreditRialResult ?? $response;
        } catch (Throwable $e) {
            return $this->getCredit();
        }
    }

    public function getUserNumbers(): array
    {
        $client = $this->getSoapClient('users');

        try {
            $response = $client->GetUserNumbers([
                'username' => $this->username,
                'password' => $this->password,
            ]);

            $result = $response->GetUserNumbersResult ?? [];
            if (is_object($result) && isset($result->string)) {
                return (array) $result->string;
            }
            return (array) $result;
        } catch (Throwable $e) {
            return $this->defaultFrom ? [$this->defaultFrom] : [];
        }
    }

    public function getDelivery(string|int $recId): mixed
    {
        $client = $this->getSoapClient('send');

        try {
            $response = $client->GetDelivery([
                'username' => $this->username,
                'password' => $this->password,
                'recId'    => (int) $recId,
            ]);
            return $response->GetDeliveryResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای دریافت وضعیت دلیوری SOAP: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getDeliveries(array $recIds): mixed
    {
        $client = $this->getSoapClient('send');

        try {
            $response = $client->GetDeliveries2([
                'username' => $this->username,
                'password' => $this->password,
                'recID'    => array_map('intval', $recIds),
            ]);
            return $response->GetDeliveries2Result ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای دریافت وضعیت دلیوری‌های گروهی SOAP: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getMessages(int $location = 1, ?string $from = null, int $index = 0, int $count = 100): mixed
    {
        $client = $this->getSoapClient('receive');

        try {
            $response = $client->GetMessages([
                'username' => $this->username,
                'password' => $this->password,
                'location' => $location,
                'from'     => $from ?: ($this->defaultFrom ?? ''),
                'index'    => $index,
                'count'    => $count,
            ]);
            return $response->GetMessagesResult ?? $response;
        } catch (Throwable $e) {
            throw new FarapayamakException("خطای دریافت پیام‌های ورودی SOAP: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getInboxCount(bool $isRead = false): int
    {
        $client = $this->getSoapClient('receive');

        try {
            $response = $client->GetInboxCount([
                'username' => $this->username,
                'password' => $this->password,
                'isRead'   => $isRead,
            ]);
            return (int) ($response->GetInboxCountResult ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }
}
