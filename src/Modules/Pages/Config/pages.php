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
        'encode' => true,
        // crf is the quality/size tradeoff; preset is the speed/size tradeoff.
        // encoding runs synchronously in the upload request, so preset defaults
        // to medium rather than a slower, smaller-output preset
        'crf' => 32,
        'preset' => 'medium',
        // these are muted, looping background reels behind a heading overlay,
        // not footage anyone inspects closely — 1280 keeps the encode honest
        // on 1080p+ uploads instead of barely scaling them
        'maxWidth' => 1280,
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
