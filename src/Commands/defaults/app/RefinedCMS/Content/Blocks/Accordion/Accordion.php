<?php

namespace App\RefinedCMS\Content\Blocks\Accordion;

use RefinedDigital\CMS\Modules\Content\BaseContent;
use RefinedDigital\CMS\Modules\Content\Contracts\ContentInterface;
use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;

class Accordion extends BaseContent implements ContentInterface
{
    protected string $name = 'Accordion';

    public function __construct()
    {
        app(AssetAggregate::class)
            ->addStyle('accordion.css')
            ->addScript('accordion.js');
    }

    public function fields(): array
    {
        return [

        ];
    }
}
