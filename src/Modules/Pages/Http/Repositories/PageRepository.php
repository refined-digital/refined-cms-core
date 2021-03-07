<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Aggregates\PackageAggregate;
use RefinedDigital\CMS\Modules\Core\Models\Uri;
use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Pages\Models\Page;
use RefinedDigital\CMS\Modules\Pages\Models\PageContentType;
use RefinedDigital\CMS\Modules\Pages\Models\PageHolder;
use RefinedDigital\CMS\Modules\Pages\Models\Template;
use Str;

class PageRepository extends CoreRepository
{
    protected $with = [
        'content',
        'content.type',
    ];

    public function syncContent($item, $content)
    {
        $item->content()->forceDelete();
        if (is_array($content) && sizeof($content)) {
            foreach ($content as $c) {
                $item->content()->create($c);
            }
        }
    }

    // if the leaf has move holder, update the child's holder too
    public function moveChildren($id, $parent)
    {
        $new = (int) $parent['newParent'];

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
        $holders = PageHolder::orderBy('position','asc')->get();

        if ($holders && $holders->count()) {
            foreach ($holders as $pos => $holder) {

                $holder->type       = 'holder';
                $holder->children   = [];
                $holder->show       = $pos == 0 ? true : false; // if we are to show the sub pages
                $holder->on         = $pos == 0 ? true : false; // if we are on the active item
                $holder->active     = (int) $holder->active;
                $holder->position   = (int) $holder->position;

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
        $with['meta'] = function($q) {
            $q->select('template_id', 'title', 'description', 'uri');
        };

        $pages = Page::with($with)
            ->wherePageHolderId($holderId)
            ->whereParentId($parentId)
            ->orderBy('position', 'asc')
            ->get()
        ;

        if ($pages && $pages->count()) {
            foreach ($pages as $pos => $page) {
                $page = $this->formatBranch($page, $holderId, $depth);

                $data->push($page);
            }
        }

        return $data;
    }

    public function formatBranch($page, $holderId, $depth = 1)
    {
        $page->type             = 'page';
        $page->children         = [];
        $page->show             = false; // if we are to show the sub pages
        $page->on               = false; // if we are on the active item
        $page->hide_from_menu   = (int) $page->hide_from_menu;
        $page->active           = (int) $page->active;
        $page->parent_holder_id = (int) $page->parent_holder_id;
        $page->page_type        = (int) $page->page_type;
        $page->parent_id        = (int) $page->parent_id;
        $page->position         = (int) $page->position;
        $page->protected        = (int) $page->protected;
        $page->depth            = (int) $depth;

        // if we have a parent id of 0 we need to update the holder id to be negative
        if ($page->parent_id === 0) {
            $page->true_parent_id = 0;
            $page->parent_id = -$page->page_holder_id;
        }

        // format the meta to streamline for only what we need
        $meta = new \stdClass();
        $meta->template_id = (int) $page->meta->template_id;
        $meta->uri = $page->meta->uri;
        $meta->title = $page->meta->title;
        $meta->description = $page->meta->description;
        unset($page->meta);
        $page->meta = $meta;

        if ($page->content && $page->content->count()) {
            foreach ($page->content as $content) {
                $content->page_content_type_id = (int) $content->page_content_type_id;
                $content->page_id = (int) $content->id;
                $content->position = (int) $content->position;
                $content->key = 'field_'.$content->id.'_'.$content->page_id.'_'.$content->page_content_type_id;

                if ($content->type->id === 4 || $content->type->id === 5) {
                    $content->content = (int) $content->content;
                }
            }
        }

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
        $leaf->page_type = 1;
        $leaf->protected = 0;
        $leaf->children = [];
        $leaf->content = config('pages.fields');
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
            $tag->uri = $tag->type.'/'.$tagReference->uri;
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

        // abort if no page found
        if (!isset($page->id)) {
            abort(404);
        }

        // if the site is a single pager, only render the home page if its on the sitemap holder
        if (isset($settings->is_single_page) && $settings->is_single_page->value && (int) $page->page_holder_id === 1) {
            // force the home page
            $uriReference = $this->setUriReference('/');
            $pageId = $uriReference->uriable_id;
            $class = $uriReference->uriable_type;
            $original = $page;
            $page = $class::with(['meta', 'meta.template'])->find($pageId);
            $page->is_single_page = true;
            $page->original = $original;
        }

        $base = class_basename($page);
        $page->type = $base;
        $page->url = $baseHref.$uri;

        if (isset($tag)) {
            $page->tag = $tag;
            $page->tag->base = $baseHref.$uriReference->uri;
            $page->base = $baseHref.$uriReference->uri;
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
            $page->base = $baseHref.implode('/', $url);
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
            $template = 'templates::'.$page->meta->template->source;
            if (!view()->exists($template)) {
                abort(404);
            }
        }

        // todo: add permissions

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

        // link in the social media, if it exists
        if ($packages->hasPackage('SocialMedia')) {
            $page->socialMedia = socialMedia()->getForFront();
        }

        // add in some classes
        $classes = [];
        $classes[] = 'page__id--'.$page->id;
        $classes[] = 'page__template--'.Str::slug($page->meta->template->name);

        // set some extra fun stuff to the page
        $head = pages()->getPageHeaders();

        if (isset($_GET) && sizeof($_GET)) {
            $head[] = '<link rel="canonical" href="'.request()->url().'" />';
        } elseif(request()->url() != $baseHref.$page->meta->uri) {
            $head[] = '<link rel="canonical" href="'.rtrim($baseHref.$page->meta->uri, '/').'/"/>';
        } elseif(isset($page->is_single_page)) {
            $head[] = '<link rel="canonical" href="'.rtrim($baseHref, '/').'/"/>';
        }

        $page->title = (isset($page->meta->title) && $page->meta->title) ? $page->meta->title : $page->name;

        $page->head = implode("\n\t\t", $head);

        // implode the classes into a string
        $page->classes = implode(' ', $classes);

        // add in the settings
        $page->settings = $settings;

        // add the depth, based on the url slugs
        $page->depth = sizeof($uriBits);

        if ($base == 'Page') {
            $page->content;
        }

        return $page;
    }

    private function setUriReference($uri)
    {
        // the home page
        if (!$uri || $uri == '/') {
            $uriReference = Uri::whereUriableId(1)
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

    public function getPagesForMenu($holder, $parent = 0, $maxDepth = 10, $level = 1, $parentUrl = '')
    {
        $data = [];
        $pages = Page::with(['meta', 'meta.template'])
                        ->whereActive(1)
                        ->whereHideFromMenu(0)
                        ->wherePageHolderId($holder)
                        ->whereParentId($parent)
                        ->order()
                        ->get();

        $total = sizeof($pages);
        if($total) {
            $i = 0;
            // if we are at top level, parent url needs to start out as empty
            if($parent == 0) {
                $parentUrl = '';
            }
            // setting the url for the page
            $separator = '/';

            // set the base href
            // rtrim will remove the last / - i am doing this to force the / incase we set the config to have the /
            $base = rtrim(config('app.url'), '/').'/';

            foreach($pages as $page) {
                $i++;
                if($parent == 0) {
                    $parentUrl = '';
                }

                // if we are at top level, we don't want the starting slash
                if($parentUrl == '') {
                    $separator = '';
                }

                // grab the url
                $url = isset($page->meta->uri) ? $page->meta->uri : '';
                $page->url = $base.$parentUrl.$separator.$url;
                // if we are a holder, just set the path as a '#'
                if($page->page_type == '0') {
                    $page->url = request()->getUri().'#';
                }

                // set the depth
                $page->depth = $level;

                $classes = [];
                $classes[] = 'nav__item';
                $classes[] = 'nav__item--id-'.$page->id;
                $classes[] = 'nav__item--'.$i;
                $classes[] = 'nav__item--depth-'.$level;
                if($i == 1) {
                    $classes[] = 'nav__item--first';
                }
                if($i == $total) {
                    $classes[] = 'nav__item--last';
                }
                // check if parents are also active
                $bits = explode('/', str_replace($base, '', request()->url()));

                // check if we have an active state
                if(in_array($url, $bits)) {
                    $classes[] = 'nav__item--active';
                }

                // if we are on the home page, we should be active
                if (request()->url() == rtrim(config('app.url'), '/') && $page->id == 1) {
                    $classes[] = 'nav__item--active';
                }

                if($level < $maxDepth) {
                    // check if we have children
                    $pUrl = $parentUrl.$separator.$url;
                    $children = $this->getPagesForMenu($holder, $page->id, $maxDepth, $level + 1, $pUrl);
                    // only add children if we have any
                    if(sizeof($children)) {
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

        $settings = settings()->get('pages');
        $baseHref = pages()->getBaseHref();

        $page = new \stdClass();
        $page->type = $type;
        $page->url = $baseHref.request()->path();
        $page->name = $type;

        $classes = [];
        $classes[] = 'page__id--'.Str::slug($type);
        $classes[] = 'page__template--'.Str::slug($type);

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
}
