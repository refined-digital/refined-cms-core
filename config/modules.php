<?php

return [
    'Users' => [
        'Provider' => RefinedDigital\CMS\Modules\Users\Providers\UsersServiceProvider::class,
        'Aliases' => []
    ],
    'Pages' => [
        'Provider' => RefinedDigital\CMS\Modules\Pages\Providers\PageServiceProvider::class,
        'Aliases' => []
    ],
    'Config' => [
        'Provider' => RefinedDigital\CMS\Modules\SiteSettings\Providers\SiteSettingsServiceProvider::class,
        'Aliases' => []
    ],
    'Settings' => [
        'Provider' => RefinedDigital\CMS\Modules\Settings\Providers\SettingServiceProvider::class,
        'Aliases' => []
    ],
    'Media' => [
        'Provider' => RefinedDigital\CMS\Modules\Media\Providers\MediaServiceProvider::class,
        'Aliases' => []
    ],
    'Tags' => [
        'Provider' => RefinedDigital\CMS\Modules\Tags\Providers\TagServiceProvider::class,
        'Aliases' => []
    ],
];
