<?php

namespace RefinedDigital\CMS\Modules\Core\Traits;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearResponseCacheTrait
{
    public static function bootClearResponseCacheTrait()
    {
        self::created(function () {
            ResponseCache::clear();
        });

        self::updated(function () {
            ResponseCache::clear();
        });

        self::deleted(function () {
            ResponseCache::clear();
        });
    }
}
