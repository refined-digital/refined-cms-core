<?php

namespace App\RefinedCMS\Content\Blocks\Banners;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class Banners extends BaseContent implements ContentInterface
{
    protected string $name = 'Banners';

    protected string $description = 'Full width rotating banners';

    public function __construct()
    {
        app(AssetAggregate::class)->addStyle('banner.css');
    }

    public function fields(): array
    {
        return [
            [
                'name' => 'Images',
                'page_content_type_id' => PageContentType::REPEATABLE->value,
                'fields' => [
                    [
                        'name' => 'Image',
                        'page_content_type_id' => PageContentType::IMAGE->value,
                        'width' => 1920,
                        'height' => 1065,
                    ],
                    [
                        'name' => 'Mobile Image',
                        'page_content_type_id' => PageContentType::IMAGE->value,
                        'width' => 800,
                        'height' => 1150,
                    ],
                ],
            ],
        ];
    }
}
