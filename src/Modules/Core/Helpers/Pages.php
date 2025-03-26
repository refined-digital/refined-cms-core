<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Response;
use RefinedDigital\CMS\Modules\Pages\Aggregates\PageAggregate;
use RefinedDigital\CMS\Modules\Pages\Models\Page;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;

class Pages {

    public function getConfig()
    {
        $config = config('pages');

        return $config;
    }

    public function getModules()
    {
        $page = app(PageAggregate::class);
        $modules = $page->all();

        // add the config
        if (is_array($modules) && sizeof($modules)) {
            foreach ($modules as $moduleKey => $module) {
                $moduleConfig = config($module['config']);
                $modules[$moduleKey]['config'] = $moduleConfig;
                if (isset($module['fields']) && sizeof($module['fields'])) {
                    foreach ($module['fields'] as $fieldKey => $field) {
                        if (isset($moduleConfig['fields'][$field['field']])) {
                            $modules[$moduleKey]['fields'][$fieldKey]['config'] = $moduleConfig['fields'][$field['field']];
                        }
                    }
                }
            }
        }

        return $modules;
    }

    public function getPageHeaders()
    {
        $head = [];
        $head[] = '<meta charset="utf-8">';
        $head[] = '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
        $head[] = '<meta name="csrf-token" content="'.csrf_token().'">';
        $head[] = '<base href="'.$this->getBaseHref().'"/>';

        if (env('SHOPIFY_DOMAIN') && env('SHOPIFY_API_KEY')) {
            $head[] = '<meta name="robots" content="noindex,nofollow">';
        }


        return $head;
    }

    public function getBaseHref()
    {
        if (help()->isMultiTenancy() && tenant()) {
            return rtrim(request()->getSchemeAndHttpHost(), '/').'/';
        }

        return rtrim(config('app.url'), '/').'/';
    }

    public function getErrorPageVariables($title = 'Error', $statusCode = 404)
    {
        return new Page404($title, $statusCode);
    }

    public function getSubPages($parentId = 0, $hideFromMenu = false)
    {
        return Page::with(['meta','content', 'content.type'])
            ->whereActive(1)
            ->whereParentId($parentId)
            ->whereHideFromMenu($hideFromMenu)
            ->orderBy('position')
            ->get();

    }

    public function getTopLevelPage($page, $hideFromMenu = false)
    {
        $i = 0;
        $parentId = $page->parent_id;
        $newPage = $page;
        while ($parentId > 0) {
            $newPage = Page::whereId($parentId)
                ->whereHideFromMenu($hideFromMenu)
                ->first();

            if (isset($newPage->id)) {
                $parentId = $newPage->parent_id;
            }

            $i++;
            if ($i === 10) {
                break;
            }
        }

        return $newPage;
    }

    public function find($pageId)
    {
        return Page::find($pageId);
    }

    public function getPageLink($pageId)
    {
        return Page::find($pageId)->meta->uri;
    }

    public function getPageListing()
    {
        $pages = Page::with(['meta','meta.template'])
            ->whereActive(1)
            ->where('id','>', 1)
            ->wherePageHolderId(1)
            ->whereParentId(0)
            ->orderby('position','asc')
            ->get();

        return $pages;
    }

    public function getSubPageListing($pageId)
    {
        $pages = Page::with(['meta','meta.template'])
            ->whereActive(1)
            ->where('id','>', 1)
            ->wherePageHolderId(1)
            ->whereParentId($pageId)
            ->orderby('position','asc')
            ->get();
        return $pages;
    }
}
