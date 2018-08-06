<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;

class CoreController extends Controller
{
    protected $prefix;
    protected $heading = 'Set in child controller';
    protected $button = '';
    protected $table = false;
    protected $routes;
    protected $route;

    private $coreRepository;

    public function __construct(CoreRepository $coreRepository)
    {
        $this->coreRepository = $coreRepository;
        $this->coreRepository->setModel($this->model);

        $this->routes = new \stdClass();
        if ($this->route) {
            $this->routes->search = route('refined.'.$this->route.'.index');
            $this->routes->create = route('refined.'.$this->route.'.create');
            $this->routes->store = route('refined.'.$this->route.'.store');
            $this->routes->update = 'refined.'.$this->route.'.update';
            $this->routes->sort = route('refined.'.$this->route.'.position');
            $this->routes->index = route('refined.'.$this->route.'.index');
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // do the initial setting of vars on the child class
        $this->setup();

        $config = $this->getConfig();

        // get the data listing
        $data = $this->coreRepository->getAll();
        $config['data'] = $data;

        // add the table settings, if there are any
        if ($this->table) {
            $config['tableSettings'] = $this->table;
            if (!isset($config['tableSettings']->noDelete)) {
                $config['tableSettings']->noDelete = [];
            }
        }

        // check if we need to do the sorting
        if (isset($this->table->sortable) && $this->table->sortable) {
            $config['sortable'] = true;
            if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                if ($data->total() <= $data->perPage()) {
                    $config['sort'] = true;
                }
            } else {
                if (request()->has('perPage') && request()->get('perPage') == 'all') {
                    $config['sort'] = true;
                }
            }
        }

        return $this->loadView('index', $config);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $config = $this->getConfig();
        $item = new $this->model;
        $config['data'] = $item;

        return $this->loadView('create', $config);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeRecord(Request $request)
    {
        $item = $this->coreRepository->store($request);

        $route = $this->getReturnRoute($item->id, $request->get('action'));

        return redirect($route)->with('status', 'Successfully created');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $item
     * @return \Illuminate\Http\Response
     */
    public function edit($item)
    {
        $config = $this->getConfig();
        $config['data'] = $item;

        return $this->loadView('edit', $config);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateRecord($request, $id)
    {
        $this->coreRepository->update($id, $request);

        $route = $this->getReturnRoute($id, $request->get('action'));

        return redirect($route)->with('status', 'Successfully updated');
    }

    /**
     * Update positions of the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function position(Request $request)
    {
        if ($request->has('positions')) {
            $model = $this->model;
            $model::setNewOrder($request->get('positions'), 0);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($this->coreRepository->destroy($id)) {
            return back()->with('status', 'Item deleted successfully');
        }

        return back()->with('status', 'Failed to delete item')->with('fail', 1);
    }


    protected function loadView($view, $config)
    {
        // default view
        $prefix = 'core::pages.';

        // if there is a custom view, load it, else load the default one
        if (view()->exists($this->prefix.$view)) {
            $prefix = $this->prefix;
        }

        return view($prefix.$view, $config);
    }

    protected function getConfig()
    {
        $route = explode('.', \Route::currentRouteName());

        // add in the variables
        $config = [];
        $config['routes'] = $this->routes;
        $config['routeEnd'] = isset($route[2]) ? $route[2] : null;
        $config['heading'] = $this->heading;
        $config['button'] = $this->button;
        $config['sort'] = false;
        $config['sortable'] = false;

        return $config;
    }

    protected function getReturnRoute($id, $type = false)
    {
        $route = route('refined.'.$this->route.'.edit', $id);

        if ($type == 'save & return') {
            $route = route('refined.'.$this->route.'.index');
        }

        if ($type == 'save & new') {
            $route = route('refined.'.$this->route.'.create');
        }

        return $route;
    }
}
