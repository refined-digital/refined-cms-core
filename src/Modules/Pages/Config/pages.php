<?php

return [

    'show_page_anchors' => [
        'enabled' => false,
        'class' => 'page__block--',
    ],

    /*
    'redirects' => [
        'members' => [
            'register' => 'account',
        ],
        'guests' => [
            'account' => 'register',
        ],
    ],
     */

    'image' => [
        // global image quality override, default by image() is 90
        // 'quality' => 90,
        'newFormat' => true,
    ],

    // used to add settings to each page
    'page_settings' => [],
];
