<?php

namespace App\RefinedCMS\Content\Blocks\VideoBanner;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class VideoBanner extends BaseContent implements ContentInterface
{
    protected string $name = 'Video Banner';

    public function __construct()
    {
        app(AssetAggregate::class)->addStyle('banner.css');
    }

    public function fields(): array
    {
        return [
            $this->getField('heading'),
            [
                'name' => 'Video',
                'page_content_type_id' => PageContentType::IMAGE->value,
            ],
        ];
    }
}
