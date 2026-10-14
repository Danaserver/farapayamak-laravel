<?php

namespace Farapayamak\Laravel;

use Farapayamak\Laravel\Drivers\DriverInterface;
use Farapayamak\Laravel\Drivers\LogDriver;
use Farapayamak\Laravel\Drivers\RestDriver;
use Farapayamak\Laravel\Drivers\SoapDriver;
use Farapayamak\Laravel\Events\FarapayamakFailed;
use Farapayamak\Laravel\Events\FarapayamakSending;
use Farapayamak\Laravel\Events\FarapayamakSent;
use Farapayamak\Laravel\Exceptions\FarapayamakException;
use Farapayamak\Laravel\Testing\FarapayamakFake;
use Farapayamak\Laravel\Models\FarapayamakSetting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FarapayamakManager
{
    /**
     * Active drivers array.
     */
    protected array $drivers = [];

    /**
     * Config array.
     */
    protected array $config;

    /**
     * Custom dynamic config resolver callback.
     *
     * @var callable|null
     */
    protected static $configResolver = null;

    /**
     * Fake instance if testing fake is active.
     */
    protected ?FarapayamakFake $fakeInstance = null;

    public function __construct(protected mixed $app)
    {
        $this->config = $app['config']['farapayamak'] ?? [];
    }

    /**
     * Register a custom dynamic config resolver callback.
     * Farapayamak::resolveConfigUsing(fn() => [...])
     */
    public static function resolveConfigUsing(?callable $resolver): void
    {
        static::$configResolver = $resolver;
    }

    /**
     * Get the effective configuration by merging database, custom resolver, and .env fallbacks.
     */
    public function getEffectiveConfig(): array
    {
        $config = $this->config;

        // 1. Check custom user callback resolver first
        if (is_callable(static::$configResolver)) {
            $custom = call_user_func(static::$configResolver);
            if (is_array($custom)) {
                return $this->mergeResolvedConfig($config, $custom);
            }
        }

        // 2. Check Database if enabled
        if (!empty($config['database']['enabled'])) {
            try {
                $tableName = $config['database']['table'] ?? 'farapayamak_settings';
                if (Schema::hasTable($tableName)) {
                    $setting = FarapayamakSetting::current();
                    if ($setting) {
                        $dbConfig = array_filter([
                            'username' => $setting->username,
                            'password' => $setting->password,
                            'from'     => $setting->from,
                            'default'  => $setting->driver,
                            'sandbox'  => $setting->sandbox,
                            'patterns' => !empty($setting->patterns) ? $setting->patterns : null,
                        ], fn($val) => !is_null($val) && $val !== '');

                        return $this->mergeResolvedConfig($config, $dbConfig);
                    }
                }
            } catch (Throwable) {
                // If database connection fails during early bootstrap, gracefully fall back to .env
            }
        }

        return $config;
    }

    /**
     * Helper to merge resolved config with base config.
     */
    protected function mergeResolvedConfig(array $base, array $override): array
    {
        if (isset($override['patterns']) && is_array($override['patterns'])) {
            $override['patterns'] = array_merge($base['patterns'] ?? [], $override['patterns']);
        }

        return array_merge($base, $override);
    }

    /**
     * Save settings directly to the database.
     */
    public function saveSettings(array $data): ?FarapayamakSetting
    {
        $setting = FarapayamakSetting::setSettings($data);
        $this->clearDrivers();
        return $setting;
    }

    /**
     * Clear settings cache and cached drivers.
     */
    public function clearSettingsCache(): void
    {
        Cache::forget(FarapayamakSetting::CACHE_KEY);
        $this->clearDrivers();
    }

    /**
     * Set temporary credentials during runtime.
     */
    public function setCredentials(string $username, string $password, ?string $from = null, array $patterns = []): self
    {
        $this->config['username'] = $username;
        $this->config['password'] = $password;
        if ($from) {
            $this->config['from'] = $from;
        }
        if (!empty($patterns)) {
            $this->config['patterns'] = array_merge($this->config['patterns'] ?? [], $patterns);
        }

        $this->clearDrivers();

        return $this;
    }

    /**
     * Clear cached driver instances.
     */
    public function clearDrivers(): void
    {
        $this->drivers = [];
    }

    /**
     * Get a driver instance by name, or default driver.
     */
    public function driver(?string $name = null): DriverInterface
    {
        if ($this->fakeInstance !== null) {
            return $this->fakeInstance;
        }

        $effectiveConfig = $this->getEffectiveConfig();

        $name = $name ?: ($effectiveConfig['default'] ?? 'rest');

        if ($effectiveConfig['sandbox'] ?? false) {
            $name = 'log';
        }

        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name, $effectiveConfig);
        }

        return $this->drivers[$name];
    }

    /**
     * Create driver instance with effective configuration.
     */
    protected function createDriver(string $name, ?array $config = null): DriverInterface
    {
        $cfg = $config ?: $this->getEffectiveConfig();

        return match ($name) {
            'rest' => new RestDriver($cfg),
            'soap' => new SoapDriver($cfg),
            'log'  => new LogDriver($cfg),
            default => throw new FarapayamakException("درایور پیامک نامعتبر: {$name}"),
        };
    }

    /**
     * Switch to another configured account (Multi-Account).
     */
    public function account(string $accountName): self
    {
        $accounts = $this->config['accounts'] ?? [];
        if (!isset($accounts[$accountName])) {
            throw new FarapayamakException("اکانت فراپیامک یافت نشد: {$accountName}");
        }

        $mergedConfig = array_merge($this->config, $accounts[$accountName]);

        $clone = clone $this;
        $clone->config = $mergedConfig;
        $clone->drivers = [];

        return $clone;
    }

    /**
     * Activate the testing fake instance.
     */
    public function fake(): FarapayamakFake
    {
        $this->fakeInstance = new FarapayamakFake();
        return $this->fakeInstance;
    }

    // -------------------------------------------------------------
    // Core SMS API Methods
    // -------------------------------------------------------------

    /**
     * Send standard SMS message.
     */
    public function send(string|array $to, string $text, ?string $from = null, bool $isFlash = false): mixed
    {
        return $this->executeWithFailover('send', $to, function (DriverInterface $driver) use ($to, $text, $from, $isFlash) {
            return $driver->send($to, $text, $from, $isFlash);
        }, ['text' => $text, 'from' => $from, 'isFlash' => $isFlash]);
    }

    /**
     * Send approved service template / pattern (BaseServiceNumber).
     * Supports pattern alias name from config (e.g. 'otp', 'welcome') or direct integer BodyId.
     *
     * @param int|string $pattern Name or BodyId
     * @param string $to Recipient mobile
     * @param array|string $variables Values for template parameters
     */
    public function pattern(int|string $pattern, string $to, array|string $variables = []): mixed
    {
        $bodyId = $this->resolvePatternId($pattern);

        return $this->executeWithFailover('pattern', $to, function (DriverInterface $driver) use ($bodyId, $to, $variables) {
            return $driver->sendPattern($bodyId, $to, $variables);
        }, ['bodyId' => $bodyId, 'variables' => $variables]);
    }

    /**
     * Send OTP verification message.
     */
    public function otp(string $to, string $text): mixed
    {
        return $this->executeWithFailover('otp', $to, function (DriverInterface $driver) use ($to, $text) {
            return $driver->sendOtp($to, $text);
        }, ['text' => $text]);
    }

    /**
     * Send multiple SMS (Peer-to-Peer).
     */
    public function sendMultiple(array $to, array $messages, ?string $from = null): mixed
    {
        return $this->executeWithFailover('multiple', $to, function (DriverInterface $driver) use ($to, $messages, $from) {
            return $driver->sendMultiple($to, $messages, $from);
        }, ['messages' => $messages, 'from' => $from]);
    }

    /**
     * Schedule SMS for future date/time.
     */
    public function schedule(string|array $to, string $text, string $scheduleDate, ?string $from = null): mixed
    {
        return $this->executeWithFailover('schedule', $to, function (DriverInterface $driver) use ($to, $text, $scheduleDate, $from) {
            return $driver->schedule($to, $text, $scheduleDate, $from);
        }, ['text' => $text, 'scheduleDate' => $scheduleDate, 'from' => $from]);
    }

    /**
     * Send Voice TTS message (SOAP).
     */
    public function voice(string $to, string $speechText, ?string $smsText = null): mixed
    {
        return $this->executeWithFailover('voice', $to, function (DriverInterface $driver) use ($to, $speechText, $smsText) {
            if ($driver instanceof SoapDriver) {
                return $driver->sendVoice($to, $speechText, $smsText);
            }
            // If on REST, fallback to simple SMS or log
            return $driver->send($to, $speechText);
        }, ['speechText' => $speechText, 'smsText' => $smsText]);
    }

    /**
     * Get remaining credit count.
     */
    public function getCredit(): mixed
    {
        return $this->driver()->getCredit();
    }

    /**
     * Get remaining credit in Rials.
     */
    public function getCreditRial(): mixed
    {
        return $this->driver()->getCreditRial();
    }

    /**
     * Fetch user's active sender phone numbers from server.
     *
     * @return array<string>
     */
    public function getUserNumbers(): array
    {
        return $this->driver()->getUserNumbers();
    }

    /**
     * Get list of defined patterns from config.
     *
     * @return array<string, array{id: int|string, title: string, vars: array}>
     */
    public function getPatterns(): array
    {
        return $this->getEffectiveConfig()['patterns'] ?? [];
    }

    /**
     * Get delivery status for a record ID.
     */
    public function getDelivery(string|int $recId): mixed
    {
        return $this->driver()->getDelivery($recId);
    }

    /**
     * Get delivery statuses for multiple record IDs.
     */
    public function getDeliveries(array $recIds): mixed
    {
        return $this->driver()->getDeliveries($recIds);
    }

    /**
     * Get received messages.
     */
    public function getMessages(int $location = 1, ?string $from = null, int $index = 0, int $count = 100): mixed
    {
        return $this->driver()->getMessages($location, $from, $index, $count);
    }

    /**
     * Get inbox count.
     */
    public function getInboxCount(bool $isRead = false): int
    {
        return $this->driver()->getInboxCount($isRead);
    }

    /**
     * Resolve pattern key alias to its numeric BodyId.
     */
    public function resolvePatternId(int|string $pattern): int|string
    {
        if (is_numeric($pattern)) {
            return $pattern;
        }

        $patterns = $this->getPatterns();
        if (isset($patterns[$pattern]['id'])) {
            return $patterns[$pattern]['id'];
        }

        return $pattern;
    }

    /**
     * Execute operation with Event dispatches and optional REST->SOAP Failover.
     *
     * @throws Throwable
     */
    protected function executeWithFailover(string $type, string|array $to, callable $action, array $payload = []): mixed
    {
        $primaryDriverName = $this->config['default'] ?? 'rest';
        $driver = $this->driver($primaryDriverName);

        Event::dispatch(new FarapayamakSending($type, $to, $payload, $primaryDriverName));

        try {
            $response = $action($driver);
            Event::dispatch(new FarapayamakSent($type, $to, $response, $primaryDriverName));
            return $response;
        } catch (Throwable $e) {
            Event::dispatch(new FarapayamakFailed($type, $to, $e, $primaryDriverName));

            // Check if failover is enabled and we were on REST
            if (($this->config['failover'] ?? false) && $primaryDriverName === 'rest' && $this->fakeInstance === null) {
                try {
                    $fallbackDriver = $this->driver('soap');
                    Event::dispatch(new FarapayamakSending($type, $to, $payload, 'soap'));
                    $response = $action($fallbackDriver);
                    Event::dispatch(new FarapayamakSent($type, $to, $response, 'soap'));
                    return $response;
                } catch (Throwable $fallbackEx) {
                    Event::dispatch(new FarapayamakFailed($type, $to, $fallbackEx, 'soap'));
                    throw $fallbackEx;
                }
            }

            throw $e;
        }
    }

    /**
     * Direct access to REST driver.
     */
    public function rest(): RestDriver
    {
        return $this->driver('rest');
    }

    /**
     * Direct access to SOAP driver.
     */
    public function soap(): SoapDriver
    {
        return $this->driver('soap');
    }
}
