<?php

namespace App\RefinedCMS\Content\Blocks\Form;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class Form extends BaseContent implements ContentInterface
{
    protected string $name = 'Form';

    protected string $description = 'Content and Form combo';

    public function __construct()
    {
        app(AssetAggregate::class)->addStyle('form.css');
    }

    public function fields(): array
    {
        return [
            $this->getField('background'),
            $this->getField('heading'),
            $this->getField('content'),
            [
                'name' => 'Form',
                'page_content_type_id' => PageContentType::SELECT->value,
                'options' => 'forms',
            ],
        ];
    }
}
