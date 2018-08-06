<?php

namespace RefinedDigital\CMS\Modules\Tags\Http\Controllers;

use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Tags\Http\Repositories\TagRepository;

class TagController extends CoreController
{
    protected $model = 'RefinedDigital\CMS\Modules\Tags\Models\Tag';
    protected $prefix = 'tags::';
    protected $route = null;
    protected $heading = 'Tags';
    protected $button = 'a Post';

    protected $tagRepository;

    public function __construct(CoreRepository $coreRepository)
    {
        $this->tagRepository = new TagRepository();
        $this->tagRepository->setModel($this->model);

        parent::__construct($coreRepository);
    }

    public function setup() { }

    /**
     * Grab the tags
     */
    public function getAllTags()
    {
        $tags = $this->tagRepository->all();

        return response()->json($tags);
    }

}
