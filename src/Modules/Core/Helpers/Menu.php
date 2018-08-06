<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Request;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;

class Menu {

    protected $request;
    protected $pageRepository;

    protected $holder;
    protected $maxDepth = 2;
    protected $parent = 0;
    protected $level = 1;
    protected $view = 'elements.nav';

    public function __construct(Request $request, PageRepository $pageRepository)
    {
        $this->request = $request;
        $this->pageRepository = $pageRepository;
    }

    public function holder($holder = 'Sitemap')
    {
        $this->holder = $this->pageRepository->getHolder($holder);
        return $this;
    }

    public function view($view)
    {
        $this->view = $view;
        return $this;
    }

    public function get()
    {
        $holder = isset($this->holder->id) ? $this->holder->id : 1;

        $data = $this->pageRepository->getPagesForMenu($holder, $this->parent, $this->maxDepth, $this->level, '');
        return view('templates::'.$this->view)->with(compact('data'));
    }

}