<?php

return [
    'banner' => [
        'home' => [
            'active' => false,
            'width' => 1920,
            'height' => 600,
        ],
        'internal' => [
            'active' => true,
            'width' => 1200,
            'height' => 200
        ]
    ],
    'content' => [
        [
            'name' => 'Content',
            'template' => 'content',
            'description' => 'A simple Heading and Rich Editor content combo',
            'fields' => [
                [
                    'name' => 'Heading',
                    'page_content_type_id' => 3,
                ],
                [
                    'name' => 'Content',
                    'page_content_type_id' => 1
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
                    'page_content_type_id' => 4,
                    'width' => 1920,
                    'height' => 600
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
                            'height' => 600
                        ],
                    ]
                ]
            ]
        ],
        [
            'name' => 'Gallery',
            'template' => 'gallery',
            'description' => 'A gallery layout of images',
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
                            'width' => 800,
                            'height' => 600
                        ],
                    ]
                ]
            ]
        ]
    ]
];
