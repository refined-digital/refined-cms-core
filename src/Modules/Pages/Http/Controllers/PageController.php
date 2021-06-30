<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Controllers;

use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;
use Illuminate\Http\Request;

class PageController extends CoreController
{
    protected $model = 'RefinedDigital\CMS\Modules\Pages\Models\Page';
    protected $prefix = 'pages::';
    protected $route = 'pages';
    protected $heading = 'Pages';
    protected $button = '';

    protected $pageRepository;


    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->pageRepository->setModel($this->model);

        $this->routes = new \stdClass();
        $this->routes->store = route('refined.'.$this->route.'.store');
        $this->routes->update = 'refined.'.$this->route.'.update';
        $this->routes->sort = route('refined.'.$this->route.'.position');
        $this->routes->index = route('refined.'.$this->route.'.index');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $config = $this->getConfig();

        // get the data listing
        $data = $this->pageRepository->getAll();
        $config['data'] = $data;
        $config['showHeader'] = false;


        return parent::loadView('index', $config);
    }

    /**
     * Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $item = $this->pageRepository->store($request->input('page'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'msg' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => 1,
            'leaf' => $this->pageRepository->find($item->id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $item = $this->pageRepository->update($id, $request->input('page'));

            if ($request->has('parent')) {
                $this->pageRepository->moveChildren($id, $request->input('parent'));
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'msg' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => 1,
            'leaf' => $this->pageRepository->find($id)
        ]);
    }

    /**
     * Updates the parent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateParent(Request $request, $id)
    {
        try {
            $this->pageRepository->update($id, $request->input('page'));
            if ($request->has('parent')) {
                $this->pageRepository->moveChildren($id, $request->input('parent'));
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'msg' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => 1,
            'leaf' => $this->pageRepository->find($id)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($this->pageRepository->destroy($id)) {
            return response()->json([
                'success' => 1,
            ]);
        }

        return response()->json([
            'success' => 0,
            'msg'   => 'Failed to delete'
        ]);
    }

    /**
     * Gets the tree
     *
     * @return array
     */
    public function getTree()
    {
        $data = $this->pageRepository->getTree();
        $templates = $this->pageRepository->getPageTemplates();
        $types = $this->pageRepository->getContentTypes();
        $leaf = $this->pageRepository->getLeaf();
        $forms = [];

        try {
          $formRepo = new \RefinedDigital\FormBuilder\Module\Http\Repositories\FormBuilderRepository();
          $forms = $formRepo->getForTree();
        } catch (\Exception $e) {}

        return response()->json([
            'tree'      => $data,
            'templates' => $templates,
            'forms'     => $forms,
            'types'     => $types,
            'leaf'      => $leaf,
        ]);
    }

    public function getTreeBasic()
    {
        $data = $this->pageRepository->getTree();

        return response()->json($data);
    }



    /**
     * Renders the page based on the uri
     *
     * @return array
     */
    public function render($uri = false)
    {
        // the rendering of the page stuff
        $page = $this->pageRepository->findByUri($uri);

        if (class_basename($page) == 'RedirectResponse') {
            return $page;
        }

        $view = view('templates::'.$page->meta->template->source)
                    ->with(compact('page'))->render();


        session()->forget('loaded_forms');

        // return the view
        return $view;
    }

    /**
     * Attempts to generate the xml sitemap
     *
     * @return array
     */
    public function xmlSitemap()
    {
        $data = $this->pageRepository->getForXmlSitemap();

        $content = view()
                ->make('pages::xml-sitemap.index')
                ->with(compact('data'))
        ;

        return response($content)
                ->header('Content-type','text/xml')
        ;

    }
}
