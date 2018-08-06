<?php

namespace RefinedDigital\CMS\Modules\Media\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Media\Models\MediaCategory;
use RefinedDigital\CMS\Modules\Media\Models\Media;

class MediaRepository extends CoreRepository
{
    protected $mediaModel = null;

    protected $with = [];


    public function findCategory($id)
    {
        $category = MediaCategory::with($this->with)->find($id);

        if (isset($category->id)) {
            $category = $this->formatBranch($category);

            return $category;
        }

        return false;
    }

    public function getTree()
    {
        $data = collect([]);
        $holders = MediaCategory::whereParentId(0)
                            ->orderBy('position','asc')
                            ->get();

        if ($holders && $holders->count()) {
            foreach ($holders as $pos => $holder) {

                $holder->type       = 'holder';
                $holder->children   = [];
                $holder->files      = [];
                $holder->show       = $pos == 0 ? true : false; // if we are to show the sub pages
                $holder->on         = $pos == 0 ? true : false; // if we are on the active item

                // check for children
                $children = $this->getBranch($holder->id);
                if ($children->count()) {
                    $holder->children = $children;
                }

                $data->push($holder);
            }
        }

        return $data;
    }

    public function updateParent($id, $parentId)
    {
        $item = MediaCategory::find($id);
        if (isset($item->id)) {
            $item->parent_id = $parentId;
            $item->save();
        }
    }


    public function getBranch($parentId = 0)
    {
        $data = collect([]);

        $categories = MediaCategory::with($this->with)
            ->whereParentId($parentId)
            ->orderBy('position', 'asc')
            ->get()
        ;

        if ($categories && $categories->count()) {
            foreach ($categories as $pos => $category) {
                $category = $this->formatBranch($category);

                $data->push($category);
            }
        }

        return $data;
    }

    public function getCategoryFiles($categoryId = 0)
    {
        return Media::whereMediaCategoryId($categoryId)
                            ->orderBy('position', 'asc')
                            ->get();
    }

    public function formatBranch($category)
    {
        $category->type     = 'holder';
        $category->children = [];
        $category->files    = [];
        $category->show     = false; // if we are to show the sub pages
        $category->on       = false; // if we are on the active item

        // check for children
        $children = $this->getBranch($category->id);
        if ($children->count()) {
            $category->children = $children;
        }

        // check for files
        $files = $this->getCategoryFiles($category->id);
        if ($files->count()) {
            $category->files = $files;
        }

        return $category;
    }

    public function getCategoryLeaf()
    {
        $model = new \RefinedDigital\CMS\Modules\Media\Models\MediaCategory();
        $leaf = new \stdClass();

        $attributes = $model->getFillable();
        foreach ($attributes as $value) {
            $leaf->{$value} = null;
        }

        $leaf->newPage = true;
        $leaf->on = false;
        $leaf->show = false;
        $leaf->name = 'New Category';
        $leaf->active = 1;
        $leaf->children = [];
        $leaf->files = [];

        return $leaf;
    }

    public function getMediaLeaf()
    {
        $model = new \RefinedDigital\CMS\Modules\Media\Models\Media();
        $leaf = new \stdClass();

        $attributes = $model->getFillable();
        foreach ($attributes as $value) {
            $leaf->{$value} = null;
        }

        $leaf->newPage = true;
        $leaf->on = false;
        $leaf->show = false;
        $leaf->name = 'New File';
        $leaf->active = 1;

        return $leaf;
    }


    public function uploadFile($request)
    {
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $title = str_replace('.'.$file->getClientOriginalExtension(), '', $file->getClientOriginalName());
        $fileName = str_slug($title).'.'.$extension;

        $fileDetails = [
            'media_category_id' => $request->get('media_category_id'),
            'active'            => 1,
            'name'              => $title,
            'mime'              => $file->getClientMimeType(),
            'file'              => $fileName,
            'alt'               => '',
            'description'       => '',
        ];

        // create the file
        $newFile = $this->store($fileDetails);

        if (isset($newFile->id)) {
            try {

                $directory = storage_path('app/public/uploads');

                // check and create the uploads directory if it does not exist
                if (!is_dir($directory)) {
                    mkdir($directory);
                }

                // create the file directory
                $directory = $directory.'/'.$newFile->id;

                // create the file directory
                if (!is_dir($directory)) {
                    mkdir($directory);
                }

                $uploadDirectory = 'public/uploads/'.$newFile->id;

                // store  the file
                $file->storeAs($uploadDirectory, $fileName);

                return $newFile;

            } catch(\Exception $e) {
                // delete the file if it fails
                $newFile->forceDelete();
            }
        }

        return false;

    }

    public function updateMediaParent($id, $parent)
    {
        if ($id && $parent) {

            // reset the positions
            $mediaFiles = $this->model::whereMediaCategoryId($parent)->orderBy('position')->get();
            $mediaIds = [];
            if ($mediaFiles && $mediaFiles->count()) {
                foreach ($mediaFiles as $file) {
                    $mediaIds[] = $file->id;
                }
            }

            $media = $this->model::find($id);
            if (isset($media->id)) {
                $media->media_category_id = $parent;
                $media->save();
                $mediaIds[] = $media->id;
            }

            // update the position
            $this->model::setNewOrder($mediaIds, 0);

        }
    }
}
