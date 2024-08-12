<?php

use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

$heading = [
    'name' => 'Heading',
    'field' => 'heading',
    'page_content_type_id' => PageContentType::PLAIN->value,
    'note' => 'Use <code>|</code> for a new line.'
];

$title = [
    'name' => 'Title',
    'field' => 'title',
    'page_content_type_id' => PageContentType::PLAIN->value,
    'note' => 'Use <code>|</code> for a new line.'
];

$content = [
    'name' => 'Content',
    'field' => 'content',
    'page_content_type_id' => PageContentType::RICH->value
];

$link = [
    'name' => 'Link',
    'field' => 'link',
    'page_content_type_id' => PageContentType::LINK->value,
];

$linkTitle = [
    'name' => 'Link Title',
    'field' => 'link_title',
    'page_content_type_id' => PageContentType::PLAIN->value,
];

$icon = [
    'name' => 'Icon',
    'page_content_type_id' => PageContentType::IMAGE->value,
    'field' => 'icon',
    'hide_label' => false,
    'note' => 'Preferred format is <code>svg</code>'
];

$background = [
    'name' => 'Background Colour',
    'page_content_type_id' => PageContentType::SELECT->value,
    'options' => [
        ['label' => 'White', 'value' => 'white'],
        ['label' => 'Grey', 'value' => 'grey'],
    ]
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


    'image' => [
        // global image quality override, default by image() is 90
        // 'quality' => 90,
        'newFormat' => true,
    ],


    'content' => [
        [
            'name' => 'Content',
            'template' => 'content',
            'fields' => [
                $heading,
                $title,
                $background,
                $content,
            ]
        ],
        [
            'name' => 'Plain Content',
            'template' => 'plain-content',
            'fields' => [
                $heading,
                $title,
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
                    'page_content_type_id' => PageContentType::SELECT->value,
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
                    'page_content_type_id' => PageContentType::IMAGE->value,
                    'width' => 1920,
                    'height' => 960
                ],
            ]
        ],
        [
            'name' => 'Banner',
            'template' => 'banner',
            'description' => 'A full width banner image',
            'fields' => [
                [
                    'name' => 'Image',
                    'page_content_type_id' => PageContentType::IMAGE->value,
                    'width' => 1920,
                    'height' => 960
                ],
                [
                    'name' => 'Mobile Image',
                    'page_content_type_id' => PageContentType::IMAGE->value,
                    'width' => 800,
                    'height' => 1150,
                    'field' => 'mobile_image'
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
                    'page_content_type_id' => PageContentType::REPEATABLE->value,
                    'fields' => [
                        [
                            'name' => 'Image',
                            'page_content_type_id' => PageContentType::IMAGE->value,
                            'field' => 'image',
                            'hide_label' => false,
                            'width' => 1920,
                            'height' => 1065
                        ],
                        [
                            'name' => 'Mobile Image',
                            'page_content_type_id' => PageContentType::IMAGE->value,
                            'width' => 800,
                            'height' => 1150,
                            'field' => 'mobile_image'
                        ],
                    ]
                ]
            ]
        ],
        [
            'name' => 'Video Banner',
            'template' => 'video-banner',
            'fields' => [
                [
                    'name' => 'Vimeo Share Link',
                    'page_content_type_id' => PageContentType::PLAIN->value,
                ],
                $heading,
            ]
        ],
    ],

    // used to add settings to each page
    'page_settings' => [],
];
