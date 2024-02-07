<?php

use RefinedDigital\CMS\Modules\Core\Helpers\Breadcrumbs;
use RefinedDigital\CMS\Modules\Core\Helpers\CoreHelper;
use RefinedDigital\CMS\Modules\Core\Helpers\Help;
use RefinedDigital\CMS\Modules\Core\Helpers\Format;
use RefinedDigital\CMS\Modules\Core\Helpers\Menu;
use RefinedDigital\CMS\Modules\Core\Helpers\Pages;
use RefinedDigital\CMS\Modules\Core\Helpers\PaymentGatewayHelper;
use RefinedDigital\CMS\Modules\Core\Helpers\RefinedFile;
use RefinedDigital\CMS\Modules\Core\Helpers\RefinedImage;
use RefinedDigital\CMS\Modules\Core\Helpers\Tags;
use RefinedDigital\CMS\Modules\Core\Helpers\RefinedSearch;
use RefinedDigital\CMS\Modules\Settings\Http\Repositories\SettingRepository;
use RefinedDigital\CMS\Modules\Users\Http\Repositories\Users;

if (!function_exists('help')) {
    function help()
    {
        return app(Help::class);
    }
}

if (!function_exists('format')) {
    function format()
    {
        return app(Format::class);
    }
}

if (!function_exists('image')) {
    function image()
    {
        return app(RefinedImage::class);
    }
}

if (!function_exists('files')) {
    function files()
    {
        return app(RefinedFile::class);
    }
}

if (!function_exists('menu')) {
    function menu()
    {
        return app(Menu::class);
    }
}

if (!function_exists('settings')) {
    function settings()
    {
        return app(SettingRepository::class);
    }
}


if (!function_exists('pages')) {
    function pages()
    {
        return app(Pages::class);
    }
}

if (!function_exists('search')) {
    function search()
    {
        return app(RefinedSearch::class);
    }
}

if (!function_exists('users')) {
    function users()
    {
        return app(Users::class);
    }
}

if (!function_exists('tags')) {
    function tags()
    {
        return app(Tags::class);
    }
}

if (!function_exists('paymentGateways')) {
    function paymentGateways()
    {
        return app(PaymentGatewayHelper::class);
    }
}

if (!function_exists('core')) {
    function core()
    {
        return app(CoreHelper::class);
    }
}

if (!function_exists('breadcrumbs')) {
    function breadcrumbs()
    {
        return app(Breadcrumbs::class);
    }
}

if (!function_exists('refined_asset')) {
    function refined_asset($path, $secure = null)
    {
        if (help()->isMultiTenancy() && function_exists('global_asset')) {
            $parsedUrl = parse_url(config('app.url'));
            $domain = config('tenancy.central_domains')[0];

            if (isset($parsedUrl['host'])) {
                $parsedUrl['host'] = $domain;
                $domain = help()->build_url($parsedUrl);
            }

            return rtrim($domain, '/').'/'.$path;
        }

        return asset($path, $secure);
    }
}