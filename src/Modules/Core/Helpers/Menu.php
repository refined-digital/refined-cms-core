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
    protected $skipValue = 0;
    protected $limitValue = 0;

    public function __construct(Request $request, PageRepository $pageRepository)
    {
        $this->request = $request;
        $this->pageRepository = $pageRepository;
    }

    public function holder($holder = 'Sitemap'): self
    {
        $this->holder = $this->pageRepository->getHolder($holder);
        return $this;
    }

    public function view($view): self
    {
        $this->view = $view;
        return $this;
    }

    public function skip($value = 0): self
    {
        $this->skipValue = $value;
        return $this;
    }

    public function limit($value = 0): self
    {
        $this->limitValue = $value;
        return $this;
    }

    public function get($activePage = false)
    {
        $holder = isset($this->holder->id) ? $this->holder->id : 1;

        $data = $this->pageRepository->getPagesForMenu(
            $holder,
            $this->parent,
            $this->maxDepth,
            $this->level,
            '',
            $this->skipValue,
            $this->limitValue
        );
        return view('templates::'.$this->view)
            ->with(compact('data'))
            ->with(compact('activePage'))
            ->with('parent', $this->parent);
    }

}
