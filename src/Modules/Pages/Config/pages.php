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
        'disk' => env('FILESYSTEM_DISK', 'public'),
    ],

    'video' => [
        'disk' => env('FILESYSTEM_DISK', 'public'),
        'encode' => true,
        'crf' => 32,
        'preset' => 'slow',
        'maxWidth' => 1920,
        'poster' => true,
        'posterQuality' => 80,
        // bits per second, as ffprobe reports it. an upload already at or under
        // this and within maxWidth is served as-is rather than re-encoded
        'skipUnder' => 1500000,
        'ffmpeg' => env('FFMPEG_PATH', 'ffmpeg'),
        'ffprobe' => env('FFPROBE_PATH', 'ffprobe'),
    ],

    // used to add settings to each page
    'page_settings' => [],
];
