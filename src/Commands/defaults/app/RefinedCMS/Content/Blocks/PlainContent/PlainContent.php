<?php

namespace App\RefinedCMS\Content\Blocks\PlainContent;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;

class PlainContent extends BaseContent implements ContentInterface
{
    protected string $name = 'Plain Content';

    public function fields(): array
    {
        return [
            $this->getField('background'),
            $this->getField('heading'),
            $this->getField('content'),
        ];
    }
}
