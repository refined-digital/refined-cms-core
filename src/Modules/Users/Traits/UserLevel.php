<?php

namespace RefinedDigital\CMS\Modules\Users\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RefinedDigital\CMS\Modules\Users\Scopes\UserLevelScope;

trait UserLevel
{

    public static function bootUserLevel()
    {
        static::addGlobalScope(new UserLevelScope());
    }

    public function userLevel() : BelongsTo
    {
        return $this->belongsTo('RefinedDigital\CMS\Modules\Users\Models\UserLevel', 'user_level_id');
    }
}