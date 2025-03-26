<?php

namespace App\RefinedCMS\Content\Blocks\Content;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class Content extends BaseContent implements ContentInterface
{
    protected string $name = 'Content';

    public function fields(): array
    {
        return [
            $this->getField('background'),
            $this->getField('heading'),
            $this->getField('content'),
            [
                'name' => 'Image',
                'page_content_type_id' => PageContentType::IMAGE->value,
                'width' => 1920,
                'height' => 960,
            ],
        ];
    }
}
