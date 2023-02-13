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

        // check if we have the pages banners module
        $pageBannerConfig = config('page-banners');
        if ($pageBannerConfig) {
            // check if we need to enable / disable the page banner
            if (
                isset($pageBannerConfig['home']['active']) && $pageBannerConfig['home']['active'] &&
                isset($pageBannerConfig['home']['replace_banner']) && $pageBannerConfig['home']['replace_banner']
            ) {
                $config['banner']['home']['active'] = false;
            }
            if (
                isset($pageBannerConfig['internal']['active']) && $pageBannerConfig['internal']['active'] &&
                isset($pageBannerConfig['internal']['replace_banner']) && $pageBannerConfig['internal']['replace_banner']
            ) {
                $config['banner']['internal']['active'] = false;
            }
        }

        // sort the content buttons by the name
        usort($config['content'], function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        if (function_exists('forms')) {
            $config['content'] = $this->formatConfigContent($config['content']);
        }

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

        return $head;
    }

    public function getBaseHref()
    {
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

    public function formatConfigContent($config)
    {
        $formContent = [];
        if (function_exists('forms')) {
            $formContent = forms()->getForSelect('content forms');
        }

        return array_map(function($content) use ($formContent) {
            if (isset($content['fields']) && is_array($content['fields']) && sizeof($content['fields'])) {
                $content['fields'] = array_map(function($field) use ($formContent) {
                    if (isset($field['options']) && $field['options'] == 'forms') {
                        $field['options'] = $formContent;
                    }

                    return $field;
                }, $content['fields']);
            }

            return $content;
        }, $config);
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

        $repo = new PageRepository();
        foreach ($pages as $page) {
            $page->content = $repo->formatPageContentForFrontend($page->the_content);
            unset($page->the_content);
        }

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
