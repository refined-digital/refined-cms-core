<?php

namespace RefinedDigital\CMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSettings
{
    public function hasSetting($key)
    {
        if (isset($page->settings->{$key}) && $page->settings->{$key}) {
            return true;
        }
    }

    public function getSetting($key)
    {
        if ($this->hasSetting($key)) {
            return $this->settings->{$key}->value;
        }

        return null;
    }

}
