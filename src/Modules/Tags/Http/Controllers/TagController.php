<?php

namespace RefinedDigital\CMS\Modules\Tags\Http\Controllers;

use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Tags\Http\Repositories\TagRepository;
use RefinedDigital\CMS\Modules\Tags\Http\Requests\TagRequest;

class TagController extends CoreController
{
    protected $model = 'RefinedDigital\CMS\Modules\Tags\Models\Tag';
    protected $prefix = 'tags::';
    protected $route = 'tags';
    protected $heading = 'Tags';
    protected $button = 'a Tag';

    protected $tagRepository;

    public function __construct(CoreRepository $coreRepository)
    {
        $this->tagRepository = new TagRepository();
        $this->tagRepository->setModel($this->model);

        parent::__construct($coreRepository);
    }

    public function setup()
    {
        $table = new \stdClass();
        $table->fields = [
            (object) [ 'name' => 'Name', 'field' => 'name', 'sortable' => true],
            (object) [ 'name' => 'Type', 'field' => 'type', 'sortable' => true],
        ];
        $table->routes = (object) [
            'edit'      => 'refined.tags.edit',
            'destroy'   => 'refined.tags.destroy'
        ];
        $table->sortable = false;

        $this->table = $table;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // do the initial setting of vars on the child class
        $data = $this->tagRepository->getAll();
        return $this->indexSetup($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($item)
    {
        // get the instance
        $data = $this->model::findOrFail($item);

        return parent::edit($data);
    }

    /**
     * Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(TagRequest $request)
    {
        return parent::storeRecord($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TagRequest $request, $id)
    {
        return parent::updateRecord($request, $id);
    }

    /**
     * Grab the tags
     */
    public function getAllTags()
    {
        $tags = $this->tagRepository->all();

        return response()->json($tags);
    }

}
