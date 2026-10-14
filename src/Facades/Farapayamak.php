<?php

namespace Farapayamak\Laravel\Facades;

use Farapayamak\Laravel\FarapayamakManager;
use Farapayamak\Laravel\Testing\FarapayamakFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed send(string|array $to, string $text, ?string $from = null, bool $isFlash = false)
 * @method static mixed pattern(int|string $pattern, string $to, array|string $variables = [])
 * @method static mixed otp(string $to, string $text)
 * @method static mixed sendMultiple(array $to, array $messages, ?string $from = null)
 * @method static mixed schedule(string|array $to, string $text, string $scheduleDate, ?string $from = null)
 * @method static mixed voice(string $to, string $speechText, ?string $smsText = null)
 * @method static mixed getCredit()
 * @method static mixed getCreditRial()
 * @method static array getUserNumbers()
 * @method static array getPatterns()
 * @method static mixed getDelivery(string|int $recId)
 * @method static mixed getDeliveries(array $recIds)
 * @method static mixed getMessages(int $location = 1, ?string $from = null, int $index = 0, int $count = 100)
 * @method static int getInboxCount(bool $isRead = false)
 * @method static void resolveConfigUsing(?callable $resolver)
 * @method static array getEffectiveConfig()
 * @method static ?\Farapayamak\Laravel\Models\FarapayamakSetting saveSettings(array $data)
 * @method static void clearSettingsCache()
 * @method static FarapayamakManager setCredentials(string $username, string $password, ?string $from = null, array $patterns = [])
 * @method static FarapayamakManager account(string $accountName)
 * @method static \Farapayamak\Laravel\Drivers\DriverInterface driver(?string $name = null)
 * @method static \Farapayamak\Laravel\Drivers\RestDriver rest()
 * @method static \Farapayamak\Laravel\Drivers\SoapDriver soap()
 * @method static FarapayamakFake fake()
 *
 * @see \Farapayamak\Laravel\FarapayamakManager
 */
class Farapayamak extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(): FarapayamakFake
    {
        return static::getFacadeRoot()->fake();
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'farapayamak';
    }
}
