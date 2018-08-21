<?php

namespace RefinedDigital\CMS\Modules\Settings\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Pages\Models\PageContentType;

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
                        'id'        => $item->id,
                        'name'      => $item->name,
                        'required'  => $item->required,
                        'page_content_type_id'  => $item->value->page_content_type_id,
                        'note'      => $item->value->note,
                        'content'   => $item->value->content,
                        'options'   => isset($item->value->options) ? $item->value->options : [],
                        'position'  => $item->position,
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

        if ($items && $items->count()) {
            foreach ($items as $item) {
                $type = isset($types[$item->value->page_content_type_id]) ? $types[$item->value->page_content_type_id] : null;

                $d = new \stdClass();
                $d->name = $item->name;
                $d->position = $item->position;
                $d->note = $item->value->note;
                $d->value = $item->value->content;
                $d->options = isset($item->value->options) ? $item->value->options : [];
                $d->type = $type;

                $data[str_slug($d->name, '_')] = $d;
            }
        }

        $data = json_decode(json_encode($data));

        return $data;
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


}
