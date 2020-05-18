<?php

use RefinedDigital\CMS\Modules\Core\Helpers\Help;
use RefinedDigital\CMS\Modules\Core\Helpers\Menu;
use RefinedDigital\CMS\Modules\Core\Helpers\Pages;
use RefinedDigital\CMS\Modules\Core\Helpers\PaymentGatewayHelper;
use RefinedDigital\CMS\Modules\Core\Helpers\RefinedFile;
use RefinedDigital\CMS\Modules\Core\Helpers\RefinedImage;
use RefinedDigital\CMS\Modules\Core\Helpers\Tags;
use RefinedDigital\CMS\Modules\Settings\Http\Repositories\SettingRepository;
use RefinedDigital\CMS\Modules\Users\Http\Repositories\Users;

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

if (! function_exists('Files')) {
    function files()
    {
        return app(RefinedFile::class);
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


if (! function_exists('pages')) {
    function pages()
    {
        return app(Pages::class);
    }
}

if (! function_exists('users')) {
    function users()
    {
        return app(Users::class);
    }
}

if (! function_exists('tags')) {
    function tags()
    {
        return app(Tags::class);
    }
}

if (! function_exists('paymentGateways')) {
    function paymentGateways()
    {
        return app(PaymentGatewayHelper::class);
    }
}
