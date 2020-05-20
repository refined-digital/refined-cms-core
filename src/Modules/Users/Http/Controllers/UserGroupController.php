<?php

namespace RefinedDigital\CMS\Modules\Users\Http\Controllers;

use Illuminate\Http\Request;
use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Users\Http\Repositories\UserGroupRepository;
use RefinedDigital\CMS\Modules\Users\Http\Requests\UserGroupRequest;

class UserGroupController extends CoreController
{
    protected $model = 'RefinedDigital\CMS\Modules\Users\Models\UserGroup';
    protected $prefix = 'users::groups';
    protected $route = 'user-groups';
    protected $heading = 'Groups';
    protected $button = 'a Group';

    protected $userGroupRepository;

    public function __construct(CoreRepository $coreRepository)
    {
        $this->userGroupRepository = new UserGroupRepository();

        parent::__construct($coreRepository);
    }

    public function setup() {

        $table = new \stdClass();
        $table->fields = [
            (object) [ 'name' => 'Name', 'field' => 'name', 'sortable' => true],
            (object) [ 'name' => 'Active', 'field' => 'active', 'sortable' => true, 'type'=> 'select', 'options' => [1 => 'Yes', 0 => 'No'], 'classes' => ['data-table__cell--active']],
        ];
        $table->routes = (object) [
            'edit'      => 'refined.user-groups.edit',
            'destroy'   => 'refined.user-groups.destroy'
        ];
        $table->sortable = false;

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
    public function store(UserGroupRequest $request)
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
    public function update(UserGroupRequest $request, $id)
    {
        return parent::updateRecord($request, $id);
    }
}
