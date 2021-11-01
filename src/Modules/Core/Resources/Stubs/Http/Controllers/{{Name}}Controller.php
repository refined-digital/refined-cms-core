<?php

namespace App\RefinedCMS\{{FullName}}\Http\Controllers;

use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use App\RefinedCMS\{{FullName}}\Http\Requests\{{Name}}Request;

class {{Name}}Controller extends CoreController
{
    protected $model = 'App\RefinedCMS\{{FullName}}\Models\{{Name}}';
    protected $prefix = '{{names}}::';
    protected $route = '{{name}}';
    protected $heading = '{{ReadableName}}';
    protected $button = 'an Item';

    public function __construct(CoreRepository $coreRepository)
    {
        parent::__construct($coreRepository);
    }

    public function setup() {

        $table = new \stdClass();
        $table->fields = [
            (object) [ 'name' => '#', 'field' => 'id', 'sortable' => true, 'classes' => ['data-table__cell--id']],
            (object) [ 'name' => 'Name', 'field' => 'name', 'sortable' => true],
            (object) [ 'name' => 'Active', 'field' => 'active', 'type'=> 'select', 'options' => [1 => 'Yes', 0 => 'No'], 'sortable' => true, 'classes' => ['data-table__cell--active']],
        ];
        $table->routes = (object) [
            'edit'      => 'refined.{{name}}.edit',
            'destroy'   => 'refined.{{name}}.destroy'
        ];
        $table->sortable = true;

        $this->table = $table;
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
    public function store({{Name}}Request $request)
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
    public function update({{Name}}Request $request, $id)
    {
        return parent::updateRecord($request, $id);
    }
}
