<?php

namespace RefinedDigital\CMS\Modules\Settings\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Media\Http\Repositories\MediaRepository;
use RefinedDigital\CMS\Modules\Pages\Models\PageContentType;
use Str;

class SettingRepository extends CoreRepository
{
    protected $settingModel = null;

    public function __construct()
    {
        $this->setModel('RefinedDigital\CMS\Modules\Settings\Models\Setting');
    }

    public function getAll()
    {
        // get all the data based on the model
        if ($this->settingModel) {
            $items = $this->model::whereModel($this->settingModel)
                                    ->orderBy('position')
                                    ->get();
            $data = collect();

            if ($items && $items->count()) {
                foreach ($items as $item) {
                    $data->push([
                        'id'                    => (int) $item->id,
                        'name'                  => $item->name,
                        'required'              => (int) $item->required,
                        'page_content_type_id'  => (int) $item->value->page_content_type_id,
                        'note'                  => $item->value->note,
                        'content'               => $item->value->content,
                        'options'               => isset($item->value->options) ? $item->value->options : [],
                        'position'              => (int) $item->position,
                    ]);
                }
            }

            return $data;
        }

        return [];
    }

    public function get($model)
    {
        $items = $this->model::whereModel($model)
                        ->orderBy('position', 'asc')
                        ->get();

        $typeData = PageContentType::all();
        $types = [];
        if ($typeData && $typeData->count()) {
            foreach($typeData as $td) {
                $types[$td->id] = $td->name;
            }
        }

        $data = [];

        $media = [];
        $mediaKeys = [];

        if ($items && $items->count()) {
            foreach ($items as $item) {
                $type = isset($types[$item->value->page_content_type_id]) ? $types[$item->value->page_content_type_id] : null;
                $key = Str::slug($item->name, '_');

                if (($item->value->page_content_type_id == 4 || $item->value->page_content_type_id == 5)){
                  $media[] = $item->value->content;
                  $mediaKeys[] = $key;
                }

                $d = new \stdClass();
                $d->name = $item->name;
                $d->position = (int) $item->position;
                $d->note = $item->value->note;
                $d->value = $item->value->content;
                $d->options = isset($item->value->options) ? $item->value->options : [];
                $d->type = $type;

                $data[$key] = $d;
            }
        }

        if (sizeof($media)) {
            // grab all the media by the ids, this is to add back into the data
            $mediaRepo = new MediaRepository();
            $mediaFiles = $mediaRepo->getByIds($media);
            if ($mediaFiles && $mediaFiles->count()) {
                $mediaFileLookup = [];

                foreach ($mediaFiles as $file) {
                    $mediaFileLookup[$file->id] = (object) $file->toArray();
                }

                foreach ($mediaKeys as $index => $mediaKey) {
                  if (isset($media[$index], $mediaFileLookup[$media[$index]]) && $data[$mediaKey]) {
                      $data[$mediaKey]->true_value = $data[$mediaKey]->value;
                      $data[$mediaKey]->value = $mediaFileLookup[$media[$index]];
                  }
                }
            }
        }

        $data = json_decode(json_encode($data));

        return $data;
    }

    public function getKeyValue($model)
    {
        $data = $this->get($model);
        $newData = [];

        foreach ($data as $key => $item) {
            $value = $item->value;
            if (is_object($item->value) && $item->type === 'Image') {
                $value = new \stdClass();
                $value->id = $item->value->external_id ?? $item->value->id;
                $value->url = $item->value->external_url ?? $item->value->link->original;
            }
            $newData[$key] = $value;
        }


        return $newData;
    }


    public function updateSettings($request, $model)
    {
        // first delete the settings for the model
        $this->model::whereModel($model)
                    ->delete();

        // now add in the content
        $data = $request->all();
        if (is_array($data) && sizeof($data)) {
            foreach ($data as $d) {
                $createData = [
                    'position'  => $d['position'],
                    'required'  => $d['required'],
                    'name'      => $d['name'],
                    'model'     => $model,
                    'value'     => [
                        'note'                  => $d['note'],
                        'content'               => $d['content'],
                        'page_content_type_id'  => $d['page_content_type_id'],
                    ]
                ];

                if (isset($d['options'])) {
                    $createData['value']['options'] = $d['options'];
                }

                $this->store($createData);
            }
        }
    }


    public function setSettingModel($model)
    {
        $this->settingModel = $model;
    }

    public function getSettingModel()
    {
        return $this->settingModel;
    }

    public function getByKeyCode($code)
    {
        $name = $this->getNameFromKey($code);

        return $this->getValueByNameAndModel($name[0], $name[1]);
    }

    public function getByKeyCodes($codes)
    {
        $names = array_map(function($code) {
            return $this->getNameFromKey($code);
        }, $codes);

        $data = [];
        foreach ($names as $name) {
            $content = $this->getValueByNameAndModel($name[0], $name[1]);
            $data[$name[2]] = $content;
        }

        return $data;
    }

    public function getNameFromKey($key)
    {
        $name = str_replace('_', ' ', $key);
        $bits = explode(':', trim($name, '[]'));
        array_shift($bits);
        return [...$bits, $key];
    }

    private function getValueByNameAndModel($model, $name)
    {
        $data = $this->model
            ::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->whereModel($model)
            ->first();

        if (!$data) {
            return null;
        }

        if ($data && isset($data->value->content) && $data->value->content) {
            return $data->value->content;
        }

        return null;
    }
}
