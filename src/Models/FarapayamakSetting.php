<?php

namespace Farapayamak\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FarapayamakSetting extends Model
{
    public const CACHE_KEY = 'farapayamak_settings_cache';

    protected $fillable = [
        'username',
        'password',
        'from',
        'driver',
        'sandbox',
        'patterns',
    ];

    protected $casts = [
        'sandbox'  => 'boolean',
        'patterns' => 'array',
    ];

    public function getTable()
    {
        return config('farapayamak.database.table', 'farapayamak_settings');
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * Get the active settings record with caching.
     */
    public static function current(): ?self
    {
        $ttl = (int) config('farapayamak.database.cache_ttl', 86400);

        return Cache::remember(self::CACHE_KEY, $ttl, function () {
            return static::query()->first();
        });
    }

    /**
     * Save or update the active settings in database.
     */
    public static function setSettings(array $data): self
    {
        $setting = static::query()->first() ?? new static();
        $setting->fill($data);
        $setting->save();

        Cache::forget(self::CACHE_KEY);

        return $setting;
    }
}
