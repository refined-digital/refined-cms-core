<?php

$heading = [
    'name' => 'Heading',
    'field' => 'heading',
    'page_content_type_id' => 3,
];

$content = [
    'name' => 'Content',
    'field' => 'content',
    'page_content_type_id' => 1
];

$link = [
    'name' => 'Link',
    'field' => 'link',
    'page_content_type_id' => 7,
];

$linkTitle = [
    'name' => 'Link Title',
    'field' => 'link_title',
    'page_content_type_id' => 3,
];

$icon = [
    'name' => 'Icon',
    'page_content_type_id' => 4,
    'field' => 'icon',
    'hide_label' => false,
    'note' => 'Preferred format is <code>svg</code>'
];

return [
    'banner' => [
        'home' => [
            'active' => false,
            'width' => 1920,
            'height' => 600,
        ],
        'internal' => [
            'active' => false,
            'width' => 1200,
            'height' => 200
        ]
    ],

    /*
    // global image quality override, default by image() is 90
    'image' => [
        'quality' => 90
    ],
    */

    'content' => [
        [
            'name' => 'Content',
            'template' => 'content',
            'description' => 'A simple Heading and Rich Editor content combo',
            'fields' => [
                $heading,
                $content,
            ]
        ],
        [
            'name' => 'Form',
            'template' => 'form',
            'description' => 'Content and Form combo',
            'fields' => [
                $heading,
                $content,
                [
                    'name' => 'Form',
                    'page_content_type_id' => 6,
                    'options' => 'forms',
                ],
            ]
        ],
        [
            'name' => 'Full Width Image',
            'template' => 'full-width-image',
            'description' => 'A full width image',
            'fields' => [
                [
                    'name' => 'Image',
                    'page_content_type_id' => 4,
                    'width' => 1920,
                    'height' => 960
                ],
            ]
        ],
        /*
        [
            'name' => 'Banner',
            'template' => 'banner',
            'description' => 'A full width banner image',
            'fields' => [
                [
                    'name' => 'Image',
                    'page_content_type_id' => 4,
                    'width' => 1920,
                    'height' => 960
                ],
            ]
        ],
        [
            'name' => 'Banners',
            'template' => 'banners',
            'description' => 'Full width rotating banners',
            'fields' => [
                [
                    'name' => 'Images',
                    'page_content_type_id' => 9,
                    'fields' => [
                        [
                            'name' => 'Image',
                            'page_content_type_id' => 4,
                            'field' => 'image',
                            'hide_label' => false,
                            'width' => 1920,
                            'height' => 960
                        ],
                    ]
                ]
            ]
        ],
        */
    ]
];
