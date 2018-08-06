<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Controllers;

use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Pages\Http\Requests\TemplateRequest;

class TemplateController extends CoreController
{
    protected $model = 'RefinedDigital\CMS\Modules\Pages\Models\Template';
    protected $prefix = 'pages::templates.';
    protected $route = 'templates';
    protected $heading = 'Templates';
    protected $button = 'a Template';

    public function setup() {

        $table = new \stdClass();
        $table->fields = [
            (object) [ 'name' => '#', 'field' => 'id', 'sortable' => true, 'classes' => ['data-table__cell--id']],
            (object) [ 'name' => 'Name', 'field' => 'name', 'sortable' => true],
            (object) [ 'name' => 'Source', 'field' => 'source', 'sortable' => true],
            (object) [ 'name' => 'Active', 'field' => 'active', 'type'=> 'select', 'options' => [1 => 'Yes', 0 => 'No'], 'sortable' => true, 'classes' => ['data-table__cell--active']],
        ];
        $table->routes = (object) [
            'edit'      => 'refined.templates.edit',
            'destroy'   => 'refined.templates.destroy'
        ];
        $table->sortable = true;

        $table->noDelete = [1,2];

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
    public function store(TemplateRequest $request)
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
    public function update(TemplateRequest $request, $id)
    {
        return parent::updateRecord($request, $id);
    }

}
