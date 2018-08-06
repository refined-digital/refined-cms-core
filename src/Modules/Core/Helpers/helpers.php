<?php

use RefinedDigital\CMS\Modules\Core\Helpers\Help;
use RefinedDigital\CMS\Modules\Core\Helpers\Menu;
use RefinedDigital\CMS\Modules\Core\Helpers\RefinedImage;
use RefinedDigital\CMS\Modules\Settings\Http\Repositories\SettingRepository;

if (! function_exists('help')) {
    function help()
    {
        return app(Help::class);
    }
}

if (! function_exists('image')) {
    function image()
    {
        return app(RefinedImage::class);
    }
}

if (! function_exists('menu')) {
    function menu()
    {
        return app(Menu::class);
    }
}

if (! function_exists('settings')) {
    function settings()
    {
        return app(SettingRepository::class);
    }
}
