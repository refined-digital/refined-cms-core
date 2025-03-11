<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAssetAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\PackageAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\SitemapXMLAggregate;
use RefinedDigital\CMS\Modules\Core\Models\Uri;
use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Pages\Models\Page;
use RefinedDigital\CMS\Modules\Pages\Models\PageContentType;
use RefinedDigital\CMS\Modules\Pages\Models\PageHolder;
use RefinedDigital\CMS\Modules\Pages\Models\Template;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType as PageContentTypeEnum;
use RefinedDigital\CMS\Modules\Pages\Traits\HasContentBlocks;
use Str;
use Illuminate\Support\Arr;

class PageRepository extends CoreRepository
{
    protected $with = [];

    public function __construct()
    {
        $this->setModel('RefinedDigital\CMS\Modules\Pages\Models\Page');
    }

    // if the leaf has move holder, update the child's holder too
    public function moveChildren($id, $parent)
    {
        $new = (int)$parent['newParent'];

        if ($parent['updated'] && $new < 0) {
            $children = Page::whereParentId($id)->get();

            if ($children && $children->count()) {
                foreach ($children as $child) {
                    $child->page_holder_id = abs($new);
                    $child->save();
                }
            }
        }

    }

    public function find($id)
    {
        $page = Page::with($this->with)->find($id);

        if (isset($page->id)) {
            $page = $this->formatBranch($page, $page->page_holder_id);

            return $page;
        }

        return false;
    }


    public function getTree()
    {
        $data = collect([]);
        $holders = PageHolder::active(1)->orderBy('position', 'asc')->get();

        if ($holders && $holders->count()) {
            foreach ($holders as $pos => $holder) {

                $holder->type = 'holder';
                $holder->children = [];
                $holder->show = $pos == 0; // if we are to show the sub pages
                $holder->on = $pos == 0; // if we are on the active item
                $holder->active = (int)$holder->active;
                $holder->position = (int)$holder->position;

                // check for children
                $children = $this->getBranch($holder->id, 0);
                if ($children->count()) {
                    $holder->children = $children;
                }

                $data->push($holder);
            }
        }

        return $data;
    }


    public function getBranch($holderId = 0, $parentId = 0, $depth = 1)
    {
        $data = collect([]);

        $with = $this->with;
        $with['meta'] = function ($q) {
            $q->select('template_id', 'title', 'description', 'uri');
        };

        $pages = Page::with($with)
            ->wherePageHolderId($holderId)
            ->whereParentId($parentId)
            ->orderBy('position', 'asc')
            ->get();

        if ($pages && $pages->count()) {
            foreach ($pages as $pos => $page) {
                $page = $this->formatBranch($page, $holderId, $depth);

                $data->push($page->toArray());
            }
        }

        return $data;
    }

    public function formatBranch($page, $holderId, $depth = 1)
    {
        $page->type = 'page';
        $page->children = [];
        $page->show = false; // if we are to show the sub pages
        $page->on = false; // if we are on the active item
        $page->hide_from_menu = (int)$page->hide_from_menu;
        $page->active = (int)$page->active;
        $page->parent_holder_id = (int)$page->parent_holder_id;
        $page->page_type = (int)$page->page_type;
        $page->parent_id = (int)$page->parent_id;
        $page->position = (int)$page->position;
        $page->protected = (int)$page->protected;
        $page->depth = (int)$depth;
        $page->content = $page->the_content ?? [];
        $page->settings = $this->formatPageSettings($page->settings);

        // if we have a parent id of 0 we need to update the holder id to be negative
        if ($page->parent_id === 0) {
            $page->true_parent_id = 0;
            $page->parent_id = -$page->page_holder_id;
        }

        // format the meta to streamline for only what we need
        $meta = new \stdClass();
        $meta->template_id = (int)$page->meta->template_id;
        $meta->uri = $page->meta->uri;
        $meta->title = $page->meta->title;
        $meta->description = $page->meta->description;
        unset($page->meta);
        $page->meta = $meta;

        // check for children
        $children = $this->getBranch($holderId, $page->id, $depth + 1);
        if ($children->count()) {
            $page->children = $children;
        }

        return $page;
    }


    public function getPageTemplates()
    {
        return Template::whereActive(1)
            ->orderBy('position')
            ->get();
    }


    public function getContentTypes()
    {
        $data = PageContentType::whereActive(1)
            ->orderBy('position')
            ->get();

        if ($data && $data->count()) {
            $items = [];
            foreach ($data as $d) {
                $items[] = [
                    'id' => $d->id,
                    'name' => $d->name,
                ];
            }

            return $items;
        }


        return [];
    }


    public function getLeaf()
    {
        $model = new \RefinedDigital\CMS\Modules\Pages\Models\Page();
        $leaf = new \stdClass();

        $attributes = $model->getFillable();
        foreach ($attributes as $value) {
            $leaf->{$value} = null;
        }

        $leaf->newPage = true;
        $leaf->on = false;
        $leaf->show = false;
        $leaf->name = 'New Page';
        $leaf->active = 1;
        $leaf->hide_from_menu = 0;
        $leaf->hide_from_sitemap = 0;
        $leaf->page_type = 1;
        $leaf->protected = 0;
        $leaf->children = [];
        $leaf->content = [];
        $leaf->meta = new \stdClass();
        $leaf->meta->template_id = 1;
        $leaf->meta->uri = '';
        $leaf->meta->title = null;
        $leaf->meta->description = null;

        return $leaf;
    }

    public function getHolder($holder)
    {
        if (is_numeric($holder)) {
            return PageHolder::find($holder);
        } else {
            return PageHolder::whereName($holder)->first();
        }
    }


    public function findByUri($uri)
    {
        $initialUri = $uri;
        // check if we have a placeholder enabled
        // todo: integrate this into the placeholder module
        $settings = settings()->get('pages');
        if (isset($settings->enable_placeholder) && $settings->enable_placeholder->value) {
            // we want to redirect to home, so if home, load the correct page
            if (!$uri || $uri == '/') {
                $uri = 'placeholder';
            } else {
                // don't skip if the page is a footer page
                $checkUriReference = Uri::whereUri($uri)->first();
                $redirect = true;
                if (isset($checkUriReference->id)) {
                    $checkPageId = $checkUriReference->uriable_id;
                    $checkClass = $checkUriReference->uriable_type;
                    $checkPage = $checkClass::with(['meta', 'meta.template'])->find($checkPageId);
                    $redirect = !(isset($checkPage->page_holder_id) && $checkPage->page_holder_id == 2);
                }

                if ($redirect) {
                    return redirect('/');
                }

            }
        }

        // we only want the final end point uri
        $uriBits = explode('/', $uri);

        if ($uri) {
            $uri = end($uriBits);
        }

        $redirects = config('pages.redirects');
        if (isset($redirects['members'][$uri]) && auth()->check() && auth()->user()->user_level_id > 2) {
            return redirect($redirects['members'][$uri]);
        }

        if (isset($redirects['guests'][$uri]) && !auth()->check()) {
            return redirect($redirects['guests'][$uri]);
        }

        $uriReference = $this->setUriReference($uri);
        $pageId = $uriReference->uriable_id;
        $class = $uriReference->uriable_type;

        // if the class is a tag, we need to find the actual page
        if ($class == 'RefinedDigital\CMS\Modules\Tags\Models\Tag') {
            $size = sizeof($uriBits) - 1;
            $tagReference = $uriReference;
            $tag = new \stdClass();
            $tag->name = $tagReference->name;
            $tag->type = $uriBits[$size - 1];
            $tag->uri = $tag->type . '/' . $tagReference->uri;
            $pageReference = $uriBits[$size - 2];
            $uriReference = Uri::whereUri($pageReference)->first();

            $uri = $tag->uri;

            if (!isset($uriReference->uriable_id)) {
                abort(404);
            }

            $pageId = $uriReference->uriable_id;
            $class = $uriReference->uriable_type;
        }

        // set the base href
        $baseHref = pages()->getBaseHref();

        $page = $class::with(['meta', 'meta.template'])->find($pageId);
        $base = class_basename($page);
        $page->type = $base;
        $page->url = $baseHref . $uri;


        // if the page is a redirect page, set the session and redirect to the home page
        if ($base == 'Page' && isset($page->page_type) && $page->page_type == 2) {
            session()->put('page-redirected-from', $page->id);
            return redirect('/');
        }

        // abort if no page found
        if (!isset($page->id)) {
            abort(404);
        }

        // if the site is a single pager, only render the home page if its on the sitemap holder
        $isSinglePage = (isset($settings->is_single_page) && $settings->is_single_page->value) || env('IS_SINGLE_PAGE');
        if ($isSinglePage && (int)$page->page_holder_id === 1) {
            // force the home page
            $uriReference = $this->setUriReference('/');
            $pageId = $uriReference->uriable_id;
            $class = $uriReference->uriable_type;
            $original = $page;
            $page = $class::with(['meta', 'meta.template'])->find($pageId);
            $page->is_single_page = true;
            $page->original = $original;
        }

        if (isset($tag)) {
            $page->tag = $tag;
            $page->tag->base = $baseHref . $uriReference->uri;
            $page->base = $baseHref . $uriReference->uri;
        }

        if (in_array(HasContentBlocks::class, class_uses_recursive($page::class))) {
            $page->content = $this->formatPageContentForFrontend($page->the_content);
            unset($page->the_content);
        }

        // if we are on a blog article, add in the base href for searching
        if ($class == 'RefinedDigital\Blog\Module\Models\Blog') {
            $slug = $uriReference->uri;
            $key = array_search($slug, $uriBits);
            $url = [];
            foreach ($uriBits as $k => $b) {
                if ($k < $key) {
                    $url[] = $b;
                }
            }

            // add it into page
            $page->base = $baseHref . implode('/', $url);
        }

        // abort if the page happens to be a holder
        if ($base == 'Page' && isset($page->page_type) && $page->page_type == 0) {
            abort(404);
        }

        // is the page active?
        // todo: show page is particular user level
        if (!$page->active) {
            abort(404);
        }

        // check if the template exists
        if (!isset($page->meta->template)) {
            abort(404);
        } else {
            // record exists, but does the view exist
            $template = 'templates::' . $page->meta->template->source;
            if (!view()->exists($template)) {
                abort(404);
            }
        }

        $page->assetAggregate = app(AssetAggregate::class);

        // todo: find a better way to do this
        // ie don't render the page to see if it has a form
        if (function_exists('forms')) {
            $content = view('templates::'.$page->meta->template->source)->with(compact('page'))->render();
            $hasForm = Str::contains($content, '<form');
            if ($hasForm) {
                $page->assetAggregate
                    ->addModuleStyle(asset('vendor/refined/form-builder/css/form.css'))
                    ->addModuleScript('form-builder', asset('vendor/refined/core/js/FormBuilder.js'), ['defer'])
                ;

                $hasRecaptcha = Str::contains($content, '_captcha');
                if ($hasRecaptcha) {
                    $page->assetAggregate->addModuleScript('recaptcha', '//www.google.com/recaptcha/api.js?render='.env('RECAPTCHA_SITE_KEY'), ['async', 'defer']);
                }
            }
        }


        // check if we need to do a listing
        $packages = app(PackageAggregate::class);
        $path = $packages->getPackage($page->meta->template->name);
        if ($path) {
            $page->listing = collect([]);

            $repo = new $path['repository']();
            $repo->setModel($path['model']);

            if (isset($tag->type)) {
                $data = $repo->getForFrontWithTags($tag->name, $tag->type);
            } else {
                $data = $repo->getForFront();
            }
            if ($data && $data->count()) {
                $page->listing = $data;
            }
        }

        // add in some classes
        $classes = [];
        $classes[] = 'page__id--' . $page->id;
        $classes[] = 'page__template--' . Str::slug($page->meta->template->name);

        // set some extra fun stuff to the page
        $head = pages()->getPageHeaders();

        if (isset($_GET) && sizeof($_GET)) {
            $head[] = '<link rel="canonical" href="' . request()->url() . '" />';
        } elseif (isset($page->page_url) && $page->page_url !== $initialUri) {
            $head[] = '<link rel="canonical" href="'.rtrim($baseHref.$page->page_url, '/').'"/>';
        } elseif (isset($page->is_single_page)) {
            $head[] = '<link rel="canonical" href="' . rtrim($baseHref, '/') . '"/>';
        }

        $page->title = (isset($page->meta->title) && $page->meta->title) ? $page->meta->title : $page->name;

        $page->head = implode("\n\t\t", $head);

        // implode the classes into a string
        $page->classes = implode(' ', $classes);

        // add the depth, based on the url slugs
        $page->depth = sizeof($uriBits);

        // format the page settings
        $page->settings = $this->formatPageSettingsForFE($page->settings);

        // add search results, if using the search template
        if (isset($page->meta->template->name) && $page->meta->template->name === 'Search') {
            $page->search = search()->getResults();
        }

        return $page;
    }

    private function setUriReference($uri)
    {
        // the home page
        if (!$uri || $uri == '/') {
            $homePageId = config('pages.home_page_override');
            $uriReference = Uri::whereUriableId($homePageId ?? 1)
                ->whereUriableType('RefinedDigital\CMS\Modules\Pages\Models\Page')
                ->first();
        } else {
            $uriReference = Uri::whereUri($uri)->first();
        }


        if (!isset($uriReference->uriable_id)) {
            abort(404);
        }

        return $uriReference;
    }

    public function getPagesForMenu($holder, $parent = 0, $maxDepth = 10, $level = 1, $parentUrl = '', $skip = 0, $limit = 0)
    {
        $data = [];
        $pages = Page::with(['meta', 'meta.template'])
            ->whereActive(1)
            ->whereHideFromMenu(0)
            ->wherePageHolderId($holder)
            ->whereParentId($parent);

        if ($skip) {
            $pages = $pages->skip($skip);
        }

        if ($limit) {
            $pages = $pages->limit($limit);
        }

        $pages = \Cache::remember('pages-get-for-menu-'.$holder.'-'.$parent, 10, fn() => $pages->order()->get());

        $total = sizeof($pages);
        if ($total) {
            $i = 0;
            // if we are at top level, parent url needs to start out as empty
            if ($parent == 0) {
                $parentUrl = '';
            }
            // setting the url for the page
            $separator = '/';

            // set the base href
            // rtrim will remove the last / - i am doing this to force the / incase we set the config to have the /
            $base = rtrim(help()->config('app.url'), '/') . '/';

            foreach ($pages as $page) {
                $i++;
                if ($parent == 0) {
                    $parentUrl = '';
                }

                // if we are at top level, we don't want the starting slash
                if ($parentUrl == '') {
                    $separator = '';
                }

                // grab the url
                $url = isset($page->meta->uri) ? $page->meta->uri : '';
                $page->url = $base . $parentUrl . $separator . $url;
                $page->slug = $url;
                // if we are a holder, just set the path as a '#'
                if ($page->page_type == '0') {
                    $page->url = request()->getUri() . '#';
                }

                // set the depth
                $page->depth = $level;

                $classes = [];
                $classes[] = 'nav__item';
                $classes[] = 'nav__item--id-' . $page->id;
                $classes[] = 'nav__item--' . $i;
                $classes[] = 'nav__item--depth-' . $level;
                if ($i == 1) {
                    $classes[] = 'nav__item--first';
                }
                if ($i == $total) {
                    $classes[] = 'nav__item--last';
                }
                // check if parents are also active
                $bits = explode('/', str_replace($base, '', request()->url()));

                // check if we have an active state
                if (in_array($url, $bits)) {
                    $classes[] = 'nav__item--active';
                }

                // if we are on the home page, we should be active
                $homePageId = config('pages.home_page_override') ?? 1;
                if (request()->url() == rtrim(config('app.url'), '/') && $page->id == $homePageId) {
                    $classes[] = 'nav__item--active';
                }

                if ($level < $maxDepth) {
                    // check if we have children
                    $pUrl = $parentUrl . $separator . $url;
                    $children = $this->getPagesForMenu($holder, $page->id, $maxDepth, $level + 1, $pUrl);
                    // only add children if we have any
                    if (sizeof($children)) {
                        $page->children = $children;
                        $classes[] = 'nav__item--has-children';
                    }
                }

                $page->classes = $classes;

                $data[] = $page;
            }
        }

        return $data;
    }

    public function setAsPage($type)
    {
        $settings = settings()->get('site-settings');
        $baseHref = pages()->getBaseHref();

        $page = new \stdClass();
        $page->type = $type;
        $page->url = $baseHref . request()->path();
        $page->name = $type;

        $classes = [];
        $classes[] = 'page__id--' . Str::slug($type);
        $classes[] = 'page__template--' . Str::slug($type);

        // set some extra fun stuff to the page
        $head = pages()->getPageHeaders();

        $page->title = $type;

        $page->head = implode("\n\t\t", $head);

        // implode the classes into a string
        $page->classes = implode(' ', $classes);

        // add in the settings
        $page->settings = $settings;

        // add the depth, based on the url slugs
        $page->depth = 1;

        return $page;

    }

    public function getForXmlSitemap()
    {
        $data = [];
        $holders = PageHolder::whereActive(1)
            // exclude hidden pages, we do this as they are orphan pages and not good for seo
            ->where('id', '!=', 3)
            ->orderby('position', 'asc')
            ->get();

        if (sizeof($holders)) {
            foreach ($holders as $h) {
                $pages = $this->getPagesForXmlSitemap($h->id, 0);
                foreach ($pages as $p) {
                    $data[] = $p;
                }
            }
        }

        // check for any services that need xml
        $modules = app(SitemapXMLAggregate::class)->get();
        if (sizeof($modules)) {
            $baseUrl = rtrim(config('app.url'), '/');
            foreach ($modules as $module) {
                $pages = $module['model']::with('meta')->whereActive(1)->get();
                $parentUrl = $module['baseUrl'];

                foreach ($pages as $d) {
                    $url = $d->meta->uri;
                    $urls = [$baseUrl, str_replace('/', '', $parentUrl), str_replace('/', '', $url)];
                    $urls = array_filter($urls);
                    $page = new \stdClass();
                    $page->url = implode('/', $urls);
                    $page->date = $d->updated_at->toAtomString();
                    $s = sizeof($urls);
                    $page->priority = $d->meta->uri === '/' ? '1.0' : $this->getXmlSitemapPriority($s);

                    $data[] = $page;
                }
            }
        }

        return $data;
    }

    public function getPagesForXmlSitemap($holderId, $parentId, $parentUrl = '/')
    {
        $data = Page::with('meta')
            ->whereActive(1)
            ->whereHideFromSitemap(0)
            ->wherePageHolderId($holderId)
            ->whereParentId($parentId)
            ->get();

        $pages = [];
        $baseUrl = rtrim(config('app.url'), '/');
        foreach ($data as $d) {
            $url = $d->meta->uri;
            $urls = [$baseUrl, str_replace('/', '', $parentUrl), str_replace('/', '', $url)];

            if (in_array('sitemap.xml', $urls)) {
                $index = array_search('sitemap.xml', $urls);
                unset($urls[$index]);
            }
            $urls = array_filter($urls);

            $page = new \stdClass();
            $page->url = implode('/', $urls);
            $page->date = $d->updated_at->toAtomString();
            $page->page_type = $d->page_type;
            $s = sizeof($urls);
            $page->priority = $d->meta->uri === '/' ? '1.0' : $this->getXmlSitemapPriority($s);

            $children = $this->getPagesForXmlSitemap($holderId, $d->id, $url);

            $page->children = $children;
            $pages[] = $page;
        }

        return $pages;
    }

    public function getXmlSitemapPriority($depth)
    {
        $depth -= 1;
        $depths = [
            1 => '0.8',
            2 => '0.7',
            3 => '0.6',
            4 => '0.5',
            5 => '0.4',
            6 => '0.3',
            7 => '0.2',
            8 => '0.1',
        ];

        return $depths[$depth] ?? '0.01';

    }

    public function formatData($data)
    {
        $data = parent::formatData($data);
        if (isset($data['content']) && $data['content']) {
            $data['content'] = array_map(function ($block) {
                $removeFields = ['description', 'id', 'key', 'width', 'height'];
                foreach ($removeFields as $f) {
                    if (isset($block[$f])) {
                        unset($block[$f]);
                    }
                }

                if (isset($block['fields'])) {
                    $block['fields'] = array_map(function ($item) use ($removeFields) {
                        $contentType = $item['page_content_type_id'];

                        unset($item['page_content_type_id']);

                        if (isset($item['fields'])) {
                            unset($item['fields']);
                        }

                        foreach ($removeFields as $f) {
                            if (isset($item[$f])) {
                                unset($item[$f]);
                            }
                        }

                        if (isset($item['content']) && is_array($item['content']) && $contentType !== PageContentTypeEnum::LINK->value) {
                            $item['content'] = array_map(function ($content) {
                                $newContent = $content;
                                foreach ($newContent as $key => $cont) {
                                    $newContent[$key] = $cont['content'];
                                }
                                return $newContent;
                            }, $item['content']);
                        }

                        return $item;
                    }, $block['fields']);
                }

                return $block;
            }, $data['content']);
        }

        return $data;
    }

    public function formatPageContentForFrontend($content)
    {
        $newContent = [];
        if (sizeof($content)) {
            foreach ($content as $field) {
                $formattedContent = $this->formatPageContentFields($field['fields']);
                if (isset($field['apiResource']) && $field['apiResource']) {
                    $formattedContent = (new $field['apiResource']($field));
                }
                $newField = new \stdClass();
                $newField->name = $field['name'];
                $newField->template = 'templates.content.' . $field['template'];
                $newField->content = $formattedContent;
                $newContent[] = $newField;
            }
        }

        return $newContent;
    }

    private function formatPageContentFields($fields)
    {
        $settingKeys = [];
        $settingKey = '[settings:';
        $isApi = isset(request()->route()->getAction()['prefix']) && request()->route()->getAction()['prefix'] === 'api';

        $newFields = new \stdClass();
        foreach ($fields as $field) {
            $content = $field['content'];
            if ($field['page_content_type_id'] !== PageContentTypeEnum::LINK->value && !is_array($content) && Str::contains($content, $settingKey) && !in_array($content, $settingKeys)) {
                $settingKeys[] = $content;
            }
            if ($field['page_content_type_id'] == PageContentTypeEnum::REPEATABLE->value) {
                $repeatableContent = [];
                foreach ($content as $row) {
                    $newRow = new \stdClass();
                    foreach ($row as $key => $value) {
                        $vContent = $value['content'];
                        if (is_string($vContent) && Str::contains($vContent, $settingKey) && !in_array($vContent, $settingKeys)) {
                            $settingKeys[] = $vContent;
                        }

                        if (is_array($vContent)) {
                            foreach ($vContent as $c) {
                                foreach ($c as $d) {
                                    if (Str::contains($d->content, $settingKey) && !in_array($d->content, $settingKeys)) {
                                        $settingKeys[] = $d->content;
                                    }
                                }
                            }
                        }

                        $vField = array_values(array_filter($field['fields'], function ($f) use ($key) {
                            return $f['field'] == $key;
                        }));

                        if (sizeof($vField) && isset($vField[0]['page_content_type_id']) && $vField[0]['page_content_type_id'] == PageContentTypeEnum::IMAGE->value) {
                            $img = new \stdClass();
                            $img->id = $vContent;
                            $img->width = $vField[0]['width'] ?? null;
                            $img->height = $vField[0]['height'] ?? null;
                            $vContent = $img;
                        }

                        if ($isApi) {
                            $newRow->{$key} = new \stdClass();
                            $newRow->{$key}->content = $vContent;
                            $newRow->{$key}->type = $vField[0]['page_content_type_id'] ?? PageContentTypeEnum::PLAIN->value;
                        } else {
                            $newRow->{$key} = $vContent;
                        }

                    }
                    $repeatableContent[] = $newRow;
                }

                $content = $repeatableContent;
            }
            if ($field['page_content_type_id'] == PageContentTypeEnum::IMAGE->value) {
                $imageId = $content;
                $content = new \stdClass();
                $content->id = $imageId;
                $content->width = $field['width'] ?? null;
                $content->height = $field['height'] ?? null;
            }

            $key = Str::slug($field['name'], '_');

            // if the content is being requested from the api, attach the content type id to the content as well
            if ($isApi) {
                $newFields->{$key} = new \stdClass();
                $newFields->{$key}->content = $content;
                $newFields->{$key}->type = $field['page_content_type_id'];
            } else {
                $newFields->{$key} = $content;
            }
        }

        if (sizeof($settingKeys)) {
            $values = settings()->getByKeyCodes($settingKeys);
            $newFieldsAsArray = json_decode(json_encode($newFields), true);
            $flattened = Arr::dot($newFieldsAsArray);
            foreach ($flattened as $key => $value) {
                if (in_array($value, $settingKeys) && isset($values[$value])) {
                    $flattened[$key] = $values[$value];
                }
            }
            $newFieldsAsArray = Arr::undot($flattened);
            // convert back to object
            $newFields = json_decode(json_encode($newFieldsAsArray));
        }

        return $newFields;
    }

    public function getForSelect($order = false, $orderDir = 'asc')
    {
        $data = $this->model::whereActive(1);

        if ($order) {
            $data->orderBy($order, $orderDir);
        } else {
            $data->order();
        }

        $data = $data->get();


        if (!$data->count()) {
            return [];
        }

        $items = [0 => 'Please Select'];
        foreach ($data as $item) {
            $items[$item->id] = $item->name;
        }

        return $items;
    }

    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    public function formatPageSettings($settings)
    {
        $settingConfig = config('pages.page_settings');

        if (!$settingConfig || $settingConfig && sizeof($settingConfig) < 1) {
            return [];
        }

        $keyedSettings = $this->formatPageSettingsForFE($settings);

        $config = [];
        foreach ($settingConfig as $setting) {
            $key = Str::slug($setting['name'], '_');
            $newSetting = $setting;
            $newSetting['content'] = isset($keyedSettings[$key])
                ? $keyedSettings[$key]
                : null;
            $config[] = $newSetting;
        }

        return $config;
    }

    public function store($request, $extra = [])
    {
        $request = $this->formatPageSettingsForSave($request);
        return parent::store($request, $extra);
    }

    public function update($id, $request, $extra = [])
    {
        $request = $this->formatPageSettingsForSave($request);
        return parent::update($id, $request, $extra);
    }

    private function formatPageSettingsForSave($request)
    {
        if(is_array($request)) {
            $data = $request;
        } else {
            $data = $request->all();
        }

        if (!isset($data['settings'])) {
            return $data;
        }

        $data['settings'] = is_array($data['settings'])
            ? array_map(function($item) {
                return [
                  'key' => Str::slug($item['name'], '_'),
                  'value' => $item['content']
                ];
            }, $data['settings'])
            : null
        ;

        return $data;
    }

    public function formatPageSettingsForFE($settings)
    {
        $keyedSettings = [];
        if (is_array($settings) && sizeof($settings)) {
            foreach ($settings as $item) {
                $keyedSettings[$item->key] = $item->value;
            }
        }

        return $keyedSettings;
    }
}
