<?php

namespace App\RefinedCMS\Content\Blocks\{{Name}};

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class {{Name}} extends BaseContent implements ContentInterface
{
    protected string $name = '{{NameWords}}';

    public function fields(): array
    {
        return [
            $this->getField('heading'),
            $this->getField('content'),
            $this->getField('link')
        ];
    }
}
