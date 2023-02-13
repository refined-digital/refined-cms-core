<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Response;
use RefinedDigital\CMS\Modules\Core\Traits\HasSettings;
use RefinedDigital\CMS\Modules\Pages\Aggregates\PageAggregate;
use RefinedDigital\CMS\Modules\Pages\Models\Page;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;

class Page404 {

    use HasSettings;

    protected $title = 'Error';
    protected $classes = '';
    protected $settings = [];
    protected $head = '';

    public function __construct($title, $statusCode = 404)
    {
        $head = pages()->getPageHeaders();

        $this->title = $title;
        $this->head = implode("\n\t\t", $head);
        $this->classes = 'page__error--'.$statusCode;
        $this->settings = settings()->get('pages');
    }
}
