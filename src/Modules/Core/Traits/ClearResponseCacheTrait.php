<?php

namespace RefinedDigital\CMS\Modules\Core\Traits;

use Silber\PageCache\Cache;

trait ClearResponseCacheTrait
{
    public static function bootClearResponseCacheTrait()
    {
        self::created(function () {
            $cache = app(Cache::class)->clear();
        });

        self::updated(function () {
            $cache = app(Cache::class)->clear();
        });

        self::deleted(function () {
            $cache = app(Cache::class)->clear();
        });
    }
}
