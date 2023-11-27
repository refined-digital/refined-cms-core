<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;
use RefinedDigital\CMS\Modules\Pages\Http\Resources\Api\PageDataResource;
use RefinedDigital\CMS\Modules\Pages\Http\Resources\Api\MenuResource;
use RefinedDigital\CMS\Modules\Pages\Http\Resources\Api\SetupResource;

class ApiController
{
    protected $model = 'RefinedDigital\CMS\Modules\Pages\Models\Page';
    protected $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->pageRepository->setModel($this->model);
    }

    /**
     * Renders the page based on the uri
     *
     * @return array
     */
    public function render($uri = false): PageDataResource
    {
        $data = $this->pageRepository->findByUri($uri);

        return new PageDataResource($data);
    }

    public function menu($id): AnonymousResourceCollection
    {
        $data = $this->pageRepository->getPagesForMenu(
            $id,
            0,
            2
        );

        return MenuResource::collection($data);
    }

    public function setup()
    {
        return new SetupResource();
    }
}