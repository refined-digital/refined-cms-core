<?php

namespace RefinedDigital\CMS\Modules\Media\Http\Controllers;

use Illuminate\Http\Request;
use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Media\Http\Repositories\MediaRepository;
use RefinedDigital\CMS\Modules\Media\Http\Requests\MediaCategoryRequest;

class MediaController extends CoreController
{
    protected $model = 'RefinedDigital\CMS\Modules\Media\Models\Media';
    protected $categoryModel = 'RefinedDigital\CMS\Modules\Media\Models\MediaCategory';
    protected $prefix = 'media::';
    protected $route = 'media';
    protected $heading = 'Media';
    protected $button = '';

    private $mediaRepository;

    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaRepository->setModel($this->model);

        $this->routes = new \stdClass();
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
        $data = $this->mediaRepository->getAll();
        $config['data'] = $data;
        $config['showHeader'] = false;

        return parent::loadView('index', $config);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $file = $this->model::find($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'msg' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => 1,
            'file' => $file
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
            $this->mediaRepository->update($id, $request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'msg' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => 1,
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
        $this->mediaRepository->setModel($this->model);
        if ($this->mediaRepository->destroy($id)) {
            return response()->json([
                'success' => 1,
            ]);
        }

        return response()->json([
            'success' => 0,
            'msg'   => 'Failed to delete'
        ]);
    }

    public function bulk(Request $request)
    {
        $this->mediaRepository->setModel($this->model);
        if ($this->mediaRepository->bulkDestroy($request->get('ids'))) {
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
     * Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function categoryStore(MediaCategoryRequest $request)
    {
        try {
            $this->mediaRepository->setModel($this->categoryModel);
            $item = $this->mediaRepository->store($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'msg' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => 1,
            'leaf' => $this->mediaRepository->findCategory($item->id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function categoryUpdate(MediaCategoryRequest $request, $id)
    {

        try {
            $this->mediaRepository->setModel($this->categoryModel);
            $this->mediaRepository->update($id, $request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'msg' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => 1,
            'leaf' => $this->mediaRepository->findCategory($id)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function categoryDestroy($id)
    {
        $this->mediaRepository->setModel($this->categoryModel);
        if ($this->mediaRepository->destroy($id)) {
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
     * Update positions of the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function categoryPosition(Request $request)
    {
        if ($request->has('positions')) {
            $model = $this->categoryModel;
            $model::setNewOrder($request->get('positions'), 0);
        }
    }

    /**
     * Update parent id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function categoryUpdateParent(Request $request, $id)
    {
        $this->mediaRepository->setModel($this->categoryModel);
        $this->mediaRepository->updateParent($id, $request->get('parentId'));
    }


    /**
     * Gets the tree
     *
     * @return array
     */
    public function getTree()
    {
        $data = $this->mediaRepository->getTree();
        $categoryLeaf = $this->mediaRepository->getCategoryLeaf();
        $mediaLeaf = $this->mediaRepository->getMediaLeaf();

        return response()->json([
            'tree'          => $data,
            'categoryLeaf'  => $categoryLeaf,
            'mediaLeaf'     => $mediaLeaf,
        ]);
    }

    /**
     * Upload file
     *
     * @return array
     */
    public function uploadFile(Request $request)
    {
        $file = $this->mediaRepository->uploadFile($request);

        if (isset($file->id)) {
            return response()->json([
                'success'   => 1,
                'file'      => $file
            ]);
        } else {
            return response('File failed to upload', 500);
        }
    }

    /**
     * update parent
     *
     * @return array
     */
    public function updateParent(Request $request)
    {
        $this->mediaRepository->updateMediaParent($request->get('media'), $request->get('media_category_id'));
        return response()->json([
            'success'   => 1
        ]);
    }
}
