<?php

namespace App\RefinedCMS\Content\Blocks\Banner;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class Banner extends BaseContent implements ContentInterface
{
    protected string $name = 'Banner';

    protected string $description = 'A full width banner image';

    public function __construct()
    {
        app(AssetAggregate::class)->addStyle('banner.css');
    }

    public function fields(): array
    {
        return [
            [
                'name' => 'Image',
                'page_content_type_id' => PageContentType::IMAGE->value,
                'width' => 1920,
                'height' => 960,
            ],
            [
                'name' => 'Mobile Image',
                'page_content_type_id' => PageContentType::IMAGE->value,
                'width' => 800,
                'height' => 1150,
                'field' => 'mobile_image',
            ],
        ];
    }
}
