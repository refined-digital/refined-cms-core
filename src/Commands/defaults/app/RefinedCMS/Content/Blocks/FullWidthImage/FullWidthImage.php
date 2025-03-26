<?php

namespace App\RefinedCMS\Content\Blocks\FullWidthImage;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class FullWidthImage extends BaseContent implements ContentInterface
{
    protected string $name = 'Full Width Image';

    public function fields(): array
    {
        return [
            [
                'name' => 'Image',
                'page_content_type_id' => PageContentType::IMAGE->value,
                'width' => 1920,
                'height' => 960,
            ],
        ];
    }
}
