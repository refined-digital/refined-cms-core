<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Response;
use RefinedDigital\CMS\Modules\Core\Models\PageAggregate;

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
        $head[] = '<base href="'.$this->getBaseHref().'"/>';

        return $head;
    }

    public function getBaseHref()
    {
        return rtrim(config('app.url'), '/').'/';
    }

    public function getErrorPageVariables($title = 'Error')
    {
        $page = new \stdClass();
        $head = pages()->getPageHeaders();

        $page->title = $title;
        $page->head = implode("\n\t\t", $head);
        $page->classes = '';
        $page->settings = settings()->get('pages');


        return $page;
    }
}