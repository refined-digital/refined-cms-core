<?php

namespace App\RefinedCMS\Content\Blocks\VideoBanner;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class VideoBanner extends BaseContent implements ContentInterface
{
    protected string $name = 'Video Banner';

    public function fields(): array
    {
        return [
            $this->getField('background'),
            $this->getField('heading'),
            [
                'name' => 'Vimeo Share Link',
                'page_content_type_id' => PageContentType::PLAIN->value,
            ],
        ];
    }
}
