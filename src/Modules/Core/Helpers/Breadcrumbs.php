<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Request;
use RefinedDigital\CMS\Modules\Core\Models\Uri;
use Str;

class Breadcrumbs
{
    protected $crumbs = [];
    protected $template = 'breadcrumbs';
    protected $homeUrl = null;

    public function __construct() {
        $this->homeUrl = rtrim(config('app.url'), '/');
    }

    public function loadFromUrl()
    {
        $url = request()->path();
        $slugs = explode('/', $url);

        $pages = Uri::with('details')->whereIn('uri', $slugs)->get();

        $data = array_flip($slugs);


        if ($pages->count()) {
            $parentUrl = [
                $this->homeUrl
            ];
            foreach ($pages as $page) {
                if (isset($page->details->name)) {
                    $parentUrl[] = $page->uri;

                    $crumb = new \stdClass();
                    $crumb->uri = implode('/', $parentUrl);
                    $crumb->name = $page->details->name;
                    $data[$page->uri] = $crumb;
                }
            }
        }

        $this->crumbs = array_values($data);

        return $this;
    }

    public function setTemplate($template = 'breadcrumbs')
    {
        $this->template = $template;
        return $this;
    }

    public function includeHome($include = true, $pageTitle = 'Home')
    {
        if ($include) {
            $homeCrumb = new \stdClass();
            $homeCrumb->uri = $this->homeUrl;
            $homeCrumb->name = $pageTitle;
            array_unshift($this->crumbs, $homeCrumb);
        }

        return $this;
    }

    public function includeCurrent($include = false)
    {
        if (!$include) {
            array_pop($this->crumbs);
        }

        return $this;
    }

    public function render()
    {
        return view()
            ->make('templates.elements.'.$this->template)
            ->with('crumbs', $this->crumbs)
            ->render();
    }
}
