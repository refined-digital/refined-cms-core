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

    protected $parentRoute;
    protected $parentModel;
    protected $parentIndex;

    protected $canCreate = true;
    protected $canDelete = true;
    protected $canUpdate = true;

    protected $buttons = [
        ['class' => 'button button--blue', 'name' => 'Save', 'href' => '#'],
        ['class' => 'button button--blue', 'name' => 'Save & Return', 'href' => '#'],
        ['class' => 'button button--blue', 'name' => 'Save & New', 'href' => '#'],
    ];

    protected $indexButtons = [];

    private $coreRepository;

    public function __construct(CoreRepository $coreRepository)
    {
        $this->coreRepository = $coreRepository;
        $this->coreRepository->setModel($this->model);

        $this->routes = new \stdClass();
        if (!app()->runningInConsole()) {
            if ($this->route) {
                if ($this->parentRoute) {
                    $route = request()->route($this->parentRoute);
                    $this->routes->search = route('refined.'.$this->route.'.index', $route);
                    $this->routes->create = route('refined.'.$this->route.'.create', $route);
                    $this->routes->store = route('refined.'.$this->route.'.store', $route);
                    $this->routes->update = 'refined.'.$this->route.'.update';
                    $this->routes->sort = route('refined.'.$this->route.'.position', $route);
                    $this->routes->index = route('refined.'.$this->route.'.index', $route);
                } else {
                    $this->routes->search = route('refined.'.$this->route.'.index');
                    $this->routes->create = route('refined.'.$this->route.'.create');
                    $this->routes->store = route('refined.'.$this->route.'.store');
                    $this->routes->update = 'refined.'.$this->route.'.update';
                    $this->routes->sort = route('refined.'.$this->route.'.position');
                    $this->routes->index = route('refined.'.$this->route.'.index');
                }
            }
        }

        $this->buttons = json_decode(json_encode($this->buttons));
        $this->indexButtons = json_decode(json_encode($this->indexButtons));

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // do the initial setting of vars on the child class
        $data = $this->coreRepository->getAll();
        return $this->indexSetup($data);
    }


    public function indexSetup($data) {
        $this->setup();

        if (isset($this->table->canDuplicate) && $this->table->canDuplicate) {
            $route = (object) [ 'route' => 'refined.'.$this->route.'.duplicate', 'name' => 'Duplicate', 'icon' => 'far fa-clone'];
            if (isset($this->table->extraActions) && is_array($this->table->extraActions)) {
                $this->table->extraActions[] = $route;
            } else {
                $this->table->extraActions = [
                    $route
                ];
            }
        }

        $config = $this->getConfig();

        // get the data listing
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

        if ($config['showEnableSorting']) {
            if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->total() <= $data->perPage()) {
                $config['showEnableSorting'] = false;
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
        $routeEnd = end($route);

        // add in the variables
        $config = [];
        $config['routes'] = $this->routes;
        $config['routeEnd'] = sizeof($route) > 1 ? $routeEnd : null;
        $config['heading'] = $this->heading;
        $config['button'] = $this->button;
        $config['sort'] = false;
        $config['sortable'] = false;
        $config['showEnableSorting'] = true;
        $config['prefix'] = $this->prefix;
        $config['canUpdate'] = $this->canUpdate;
        $config['canCreate'] = $this->canCreate;
        $config['canDelete'] = $this->canDelete;
        if (sizeof($this->buttons)) {
            $config['buttons'] = $this->buttons;
        }
        if (sizeof($this->indexButtons)) {
            $config['indexButtons'] = $this->indexButtons;
        }
        if ($this->parentRoute) {
            $repo = new CoreRepository();
            $repo->setModel($this->parentModel);
            $config['parent'] = $repo->find(request()->route($this->parentRoute));
            if ($this->parentIndex) {
                $config['parent']->index = route('refined.'.$this->parentIndex);
            }
        }

        if (!$this->canCreate) {
            $config['buttons'] = array_filter($config['buttons'], function($button) {
                return $button->name != 'Save & New';
            });
        }

        return $config;
    }

    protected function getReturnRoute($id, $type = false, $parentId = false)
    {
        $route = route('refined.'.$this->route.'.edit', $id);

        if ($type == 'save & return') {
            $route = route('refined.'.$this->route.'.index');
        }

        if ($type == 'save & new') {
            $route = route('refined.'.$this->route.'.create');
        }

        if ($type == 'save & edit fields') {
            $route = route('refined.'.$this->route.'.fields.index', $id);
        }

        return $route;
    }

    public function formatGetForFront($data, $request)
    {
        $template = $request->has('template') ? $request->get('template') : 'templates.elements.article';
        if ($data && $data->count()) {
            $formatted = [];
            $templateVariables = [];
            if ($request->has('templateVariables')) {
                foreach ($request->get('templateVariables') as $var) {
                    $bits = explode(':', $var);
                    $templateVariables[$bits[0]] = $bits[1];
                }
            }

            foreach($data as $d) {
                $formatted[] =view()
                    ->make($template)
                    ->with('article', $d)
                    ->with($templateVariables)
                    ->render();
            }

            return response()->json([
                'success' => 1,
                'items' => $formatted,
                'done' => $request->get('page') < $data->lastPage() ? false : true
            ]);
        }

        return response()->json(['success' => 0]);
    }


    public function setCanCreate($option)
    {
        $this->canCreate = $option;
    }

    public function setCanDelete($option)
    {
        $this->canDelete = $option;
    }

    public function setCanUpdate($option)
    {
        $this->canUpdate = $option;
    }

    public function getButtons()
    {
        return $this->buttons;
    }

    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
    }

    public function duplicate($originalId)
    {
        $original = $this->model::find($originalId);

        if (!isset($original->id)) {
            return redirect()->back()->with('status', 'Failed to find the original')->with('fail', true);
        }

        $new = $original->replicate();
        $new->active = false;

        if ($new->name) {
            $new->name .= ' - DUPLICATE';
        }

        $this->coreRepository->store($new->toArray());

        return redirect()->back()->with('status', 'Successfully duplicated');
    }
}
