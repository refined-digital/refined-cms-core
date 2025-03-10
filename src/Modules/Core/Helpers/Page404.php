<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Response;
use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;
use RefinedDigital\CMS\Modules\Core\Traits\HasSettings;
use RefinedDigital\CMS\Modules\Pages\Aggregates\PageAggregate;
use RefinedDigital\CMS\Modules\Pages\Models\Page;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;

class Page404 {

    use HasSettings;

    public $title = 'Error';
    public $classes = '';
    public $settings = [];
    public $head = '';

    public function __construct($title, $statusCode = 404)
    {
        $head = pages()->getPageHeaders();

        $this->title = $title;
        $this->head = implode("\n\t\t", $head);
        $this->classes = 'page__error--'.$statusCode;
        $this->settings = settings()->get('pages');
        $this->assetAggregate = app(AssetAggregate::class);
    }
}
