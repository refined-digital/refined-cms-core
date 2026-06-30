<?php

namespace RefinedDigital\CMS\Modules\Core\Traits;

trait BackgroundImageTrait
{
    public function getBackgroundImage()
    {
        $background = settings()->getByKeyCode('[settings:site-settings:Login Image]');
        $image = null;
        $settings = settings()->getAll();
        if ($background) {
            $image = image()->load($background)->string();
        }

        return $image;
    }
}
