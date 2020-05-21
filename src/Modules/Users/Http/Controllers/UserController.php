<?php

namespace RefinedDigital\CMS\Modules\Users\Http\Controllers;

use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Users\Http\Repositories\UserRepository;
use RefinedDigital\CMS\Modules\Users\Http\Requests\UserRequest;
use RefinedDigital\CMS\Modules\Users\Models\User;

class UserController extends CoreController
{
    protected $model = 'RefinedDigital\CMS\Modules\Users\Models\User';
    protected $prefix = 'users::index.';
    protected $route = 'users';
    protected $heading = 'Users';
    protected $button = 'User';

    protected $userRepository;

    public function __construct(CoreRepository $coreRepository)
    {
        $this->userRepository = new UserRepository();
        $this->userRepository->setModel($this->model);

        parent::__construct($coreRepository);
    }

    public function setup() {

        $table = new \stdClass();
        $table->fields = [
            (object) [ 'name' => 'First Name', 'field' => 'first_name', 'sortable' => true],
            (object) [ 'name' => 'Last Name', 'field' => 'last_name', 'sortable' => true],
            (object) [ 'name' => 'Email', 'field' => 'email', 'sortable' => true],
            (object) [ 'name' => 'User Level', 'field' => 'user_level_id', 'type' => 'userLevel', 'sortable' => true],
            (object) [ 'name' => 'Active', 'field' => 'active', 'sortable' => true, 'type'=> 'select', 'options' => [1 => 'Yes', 0 => 'No'], 'classes' => ['data-table__cell--active']],
        ];
        $table->routes = (object) [
            'edit'      => 'refined.users.edit',
            'destroy'   => 'refined.users.destroy'
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
        $repo = new UserRepository();
        $repo->setModel($this->model);
        $data = $repo->getAll();
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
        $data = User::findOrFail($item);
        return parent::edit($data);
    }

    /**
     * Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $item = $this->userRepository->store($request);

        $this->userRepository->syncUserGroups($item->id, $request->get('groups'));
        $route = $this->getReturnRoute($item->id, $request->get('action'));

        return redirect($route)->with('status', 'Successfully created');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        $this->userRepository->update($id, $request);

        $this->userRepository->syncUserGroups($id, $request->get('groups'));

        $route = $this->getReturnRoute($id, $request->get('action'));

        return redirect($route)->with('status', 'Successfully updated');
    }

}
