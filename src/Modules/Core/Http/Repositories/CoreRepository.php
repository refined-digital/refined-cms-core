<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RefinedDigital\CMS\Modules\Media\Http\Repositories\MediaRepository;
use RefinedDigital\CMS\Modules\Tags\Models\Tag;

class CoreRepository {

    protected $model;

    private $dates = ['date'];
    private $dateTimes = [];

    public function setModel($model)
    {
        $this->model = $model;
    }

	public function getAll()
    {
        return $this->model::
            keywords()
            ->order()
            ->paging()
        ;
    }

    public function getForFront()
    {
        $data = $this->model::whereActive(1)
            ->order()
            ->get();

        return $data;
    }

    public function find($id)
    {
        return $this->model::find($id);
    }

    public function destroy($id)
    {
        $model = $this->model;
        $item = $model::find($id);
        if(isset($item->id)) {
            $item->delete();

            // log
            activity()
                ->performedOn($item)
                ->causedBy(auth()->check() ? auth()->user()->id : null)
                ->withProperties(['Deleted item' => $id])
                ->log('An item was deleted')
            ;

            // send back a note to say all good
            return true;
        }

        // return false as we failed
        return false;

    }

    public function update($id, $request, $extra = [])
	  {
		$model = $this->model;
        $item = $model::findOrFail($id);

        if(is_array($request)) {
            $data = $request;
        } else {
            $data = $request->all();
        }

        $basename = class_basename($item);

        if(is_array($extra) && sizeof($extra)) {
            $data = array_merge($data, $extra);
        }

        if ($basename !== 'Page' && method_exists($item, 'findImageFields')) {
          // handle any new file uploads
          $data = $this->handleMediaFiles($data, $item);
        }

        if ($basename === 'Page') {
            $data = $this->handlePageBannerMediaFile($data, $item);
            $data = $this->handleMediaFilesViaContent($data, $item);
        }

        // format the data
        $data = $this->formatData($data);


        // save the item
        $item->fill($data);
        $item->save();

        // now log the item
        activity()
            ->performedOn($item)
            ->causedBy(auth()->check() ? auth()->user()->id : null)
            ->withProperties([$basename.' has been updated' => $id])
            ->log($basename.' has been updated')
        ;

        return $item;
    }

    public function store($request, $extra = [])
	  {
		$model = $this->model;

        if(is_array($request)) {
            $data = $request;
        } else {
            $data = $request->all();
        }

        if(is_array($extra) && sizeof($extra)) {
            $data = array_merge($data, $extra);
        }

        // set the initial media fields to 0 to enable save of any required fields
        $tempModel = new $this->model();
        $basename = class_basename($tempModel);
        $fieldData = $data;
        if ($basename !== 'Page' && method_exists($tempModel, 'findImageFields')) {
            $mediaFields = $tempModel->findImageFields();
            if (sizeof($mediaFields)) {
                foreach ($mediaFields as $field) {
                    if (isset($data[$field])) {
                        $data[$field] = 0;
                    }
                }
            }
        }

        // reset the images to remove their arrays on image fields
        if ($basename === 'Page') {
            $d = $this->removePageMediaFields($data);
            $pageMediaFields = $d['fields'];
            $data = $d['data'];
            $d = $this->removePageBannerMediaField($data);
            $data = $d['data'];
            $pageMediaFields = array_merge($pageMediaFields, $d['fields']);
        }

        // format the data
        $data = $this->formatData($data);

        // save the item
        $item = $model::create($data);

        if ($basename === 'Page') {
          $this->handlePageBannerMediaFile($pageMediaFields, $item);
          $this->handleMediaFilesViaContent($pageMediaFields, $item);
        }

        // re-save the media items with their actual id
        if (isset($fieldData, $mediaFields) && sizeof($mediaFields)) {
            $newFieldData = $this->handleMediaFiles($fieldData, $item);
            $update = [];

            foreach ($mediaFields as $field) {
                if (isset($newFieldData[$field])) {
                    $update[] = $field;
                    $item->{$field} = $newFieldData[$field];
                }
            }

            if (sizeof($update)) {
                $item->save();
            }
        }

        // now log the item
        $basename = class_basename($item);
        activity()
            ->performedOn($item)
            ->causedBy(auth()->check() ? auth()->user()->id : null)
            ->withProperties([$basename.' has been created' => $item->id])
            ->log($basename.' has been created')
        ;

        return $item;
    }

    private function handleMediaFiles($data, $item)
    {
        $mediaFields = $item->findImageFields();

        return $this->handleMedia($data, $mediaFields, $item);
    }

    private function handlePageBannerMediaFile($data, $item)
    {
        $mediaFields = ['banner'];
        foreach ($mediaFields as $file) {
            if (isset($data[$file])) {
                if (is_numeric($data[$file])) {
                    continue;
                }
                if (is_object($data[$file])) {
                    $data[$file] = (array) $data[$file];
                }
                $data[$file]['model'] = (object) ['name' => 'RefinedDigital\CMS\Modules\Pages\Models\Page'];
            }
        }

        return $this->handleMedia($data, $mediaFields, $item);

    }

    private function handleMedia($data, $mediaFields, $item = false)
    {
        $mediaRepository = new MediaRepository();
        $fields = [];
        if (sizeof($mediaFields)) {
            foreach ($mediaFields as $field) {
                if (isset($data[$field])) {
                    if (is_numeric($data[$field])) {
                        continue;
                    }
                    $fieldData = (is_object($data[$field]) || is_array($data[$field])) ? $data[$field] : json_decode($data[$field]);
                    if (is_array($fieldData)) {
                        $fieldData = (object) $fieldData;
                    }
                    // reset the field data to the file id
                    $data[$field] = $fieldData->id;
                    $fields[$field] = $fieldData;

                    // update the media alt text data
                    if ($item) {
                        if ($fieldData->alt !== $fieldData->fileAlt) {
                            $modelId = $fieldData->model->id ?? $item->id;
                            $mediaRepository->setAltText($fieldData->id, $modelId, $fieldData->model->name, $fieldData->alt, $field);
                        }
                    }
                }
            }
        }

        if ($item) {
            return $data;
        } else {
            return [
                'data' => $data,
                'fields' => $fields
            ];
        }
    }

    private function removePageBannerMediaField($data)
    {
        $d = $this->handleMedia($data, ['banner']);
        return [
            'fields' => $d['fields'],
            'data' => $d['data']
        ];

    }

    private function removePageMediaFields($data)
    {
        $d = $this->handleMediaFilesViaContent($data);
        return [
            'fields' => $d['fields'],
            'data' => $d['data']
        ];
    }

    private function handleMediaFilesViaContent($data, $item = false)
    {
        $keysToSearch = ['data', 'content'];
        $mediaRepository = new MediaRepository();
        $fields = [];
        foreach ($keysToSearch as $key) {
            if (isset($data[$key])) {
                $sectionData = $data[$key];
                $sectionDots = array_dot($sectionData);
                $fieldDots = array_dot($sectionData);
                $fields[$key] = [];
                if (is_array($sectionDots) && sizeof($sectionDots)) {
                    $imgKeys = [];
                    foreach ($sectionDots as $k => $v) {
                        if (is_numeric(strpos($k, 'page_content_type_id')) && $v == 4) {
                            $imageKey = str_replace('page_content_type_id', 'content', $k);
                            $imgKeys[] = $imageKey;
                        }
                    }

                    if (sizeof($imgKeys)) {
                        foreach ($imgKeys as $iKey) {

                            if (
                                array_key_exists($iKey.'.id', $sectionDots) &&
                                array_key_exists($iKey.'.fileAlt', $sectionDots) &&
                                array_key_exists($iKey.'.alt', $sectionDots) &&
                                array_key_exists($iKey.'.model.name', $sectionDots)) {
                                $alt = $sectionDots[$iKey.'.alt'];
                                $fileAlt = $sectionDots[$iKey.'.fileAlt'];
                                $modelName = $sectionDots[$iKey.'.model.name'];
                                $mediaId = $sectionDots[$iKey.'.id'];
                                if ($item) {
                                    if ($alt !== $fileAlt) {
                                        $field = $iKey;
                                        $mediaRepository->setAltText($mediaId, $item->id, $modelName, $alt, $field);
                                    }
                                }

                                foreach ($sectionDots as $sdKey => $v) {
                                    if(is_numeric(strpos($sdKey, $iKey))) {
                                        unset($sectionDots[$sdKey]);
                                    }
                                }
                                $sectionDots[$iKey] = $mediaId;

                                foreach ($fieldDots as $k => $v) {
                                    if(is_numeric(strpos($k, $iKey))) {
                                        array_set( $fields[$key], $k, $v );
                                        $fields[$key][str_replace('.content', '.page_content_type_id', $iKey)] = 4;
                                    }
                                }
                            }
                        }

                    }

                    $newData = [];
                    foreach ($sectionDots as $k => $v) {
                      array_set($newData, $k, $v);
                    }
                    $data[$key] = $newData;
                }
            }
        }

        if ($item) {
            return $data;
        } else {
            return [
                'data' => $data,
                'fields' => $fields
            ];
        }

    }



    public function formatData($data)
    {

        // force the first name
        if(isset($data['name']))            $data['name']           = ucfirst($data['name']);
        if(isset($data['first_name']))      $data['first_name']     = ucfirst($data['first_name']);
        if(isset($data['password'])) {
            if ($data['password']) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }
        } else {
            unset($data['password']);
        }

        if (isset($data['parent_id']) && isset($data['page_holder_id'])) {
            if ($data['parent_id'] < 0) {
                $data['page_holder_id'] = abs($data['parent_id']);
                $data['parent_id'] = 0;
            }
        }

        // convert the date strings
        if(sizeof($this->dates)) {
            foreach($this->dates as $date) {
                if(isset($data[$date]) && $data[$date] != '') {

                    $key = 'd/m/Y';
                    if(strlen($data[$date]) == 19 || is_numeric(strpos($data[$date], '-'))) {
                        $key = 'Y-m-d H:i:s';
                    }
                    if(strlen($data[$date]) == 11 || is_numeric(strpos($data[$date], '-'))) {
                        $key = 'Y-m-d';
                    }
                    $data[$date] = Carbon::createFromFormat($key, $data[$date]);
                }
            }
        }

        // convert the date time strings
        if(sizeof($this->dateTimes)) {
            foreach($this->dateTimes as $date) {
                if(isset($data[$date]) && $data[$date] != '') {
                    $key = 'd/m/Y H:i:s';
                    if(is_numeric(strpos($data[$date], '-'))) {
                        $key = 'Y-m-d H:i:s';
                    }
                    $data[$date] = Carbon::createFromFormat($key, $data[$date]);
                }
            }
        }

        if (isset($data['reply_to_type']) && $data['reply_to_type'] != 'text') {
            $data['reply_to'] = $data['reply_to_type'];
        }

        // check for extra data fields
        if (sizeof($data)) {
            $extraData = [];
            foreach ($data as $key => $value) {
                if (is_numeric(strpos($key, 'data__'))) {
                    $newKey = str_replace('data__', '', $key);
                    $extraData[$newKey] = $value;
                    unset($data[$key]);
                }
            }

            if (sizeof($extraData)) {
                $data['data'] = $extraData;
            }
        }

        return $data;
    }



    protected function getTagCollection($type, $model = false)
    {
        if ($model) {
            $tags = DB::table('taggables')
                        ->select('tag_id')
                        ->whereTaggableType($model)
                        ->groupBy('tag_id')
                        ->get();
        }

        $tag = Tag::with('meta')->whereType($type);
        if (isset($tags) && $tags) {
            $tag->whereIn('id', $tags->pluck('tag_id'));
        }
        $data = $tag->orderBy('position')->get();

        if ($data && $data->count()) {
            foreach ($data as $tag) {
                if (isset($tag->meta->original_uri)) {
                    continue;
                }

                $tag->meta->original_uri = $tag->meta->uri;
                $tag->meta->uri = str_slug($type).'/'.$tag->meta->uri;
            }
        }

        return $data;
    }


    public function count()
    {
        return $this->model::count();
    }
}
