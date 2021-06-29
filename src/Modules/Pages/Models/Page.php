<?php

namespace RefinedDigital\CMS\Modules\Pages\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Pages\Traits\IsPage;
use RefinedDigital\CMS\Modules\Pages\Traits\SortablePageTrait;
use Spatie\EloquentSortable\Sortable;

class Page extends CoreModel implements Sortable
{
    use SoftDeletes, IsPage, SortablePageTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_holder_id', 'parent_id', 'active', 'hide_from_menu', 'protected',
        'page_type', 'form_id', 'position', 'name', 'banner', 'data', 'content'
    ];

    protected $appends = [ 'banner_image', 'the_content' ];

    protected $casts = [
        'id' => 'integer',
        'active' => 'integer',
        'position' => 'integer',
        'page_holder_id' => 'integer',
        'parent_id' => 'integer',
        'hide_from_menu' => 'integer',
        'protected' => 'integer',
        'page_type' => 'integer',
        'form_id' => 'integer',
        'banner' => 'integer',
        'data' => 'object',
        'content' => 'object',
    ];

    public function getTheContentAttribute() {
        $data = $this->content;
        $setup = config('pages.content');
        $imageAlts = \DB::table('media_alt_text')->whereTypeDetails(get_class($this))
            ->whereTypeId($this->id)
            ->get();
        $imageAltsLookup = [];
        if ($imageAlts->count()) {
            foreach ($imageAlts as $img) {
                $imageAltsLookup[$img->field_name] = $img->alt;
            }
        }

        $setupLookup = [];
        if ($setup && sizeof($setup)) {
            foreach ($setup as $field) {
                $setupLookup[$field['name']] = $field;
            }
        }

        $formattedData = [];
        if ($data && sizeof($data)) {
            foreach ($data as $field) {
                $newField = $setupLookup[$field->name];
                $newField['id'] = uniqid();
                $newField['key'] = uniqid();
                if (isset($newField['fields'], $field->fields) && sizeof($newField['fields']) && sizeof($field->fields)) {
                    $newField['fields'] = array_map(function($item) use ($field) {
                        $block = array_values(array_filter($field->fields, function ($nBlock) use($item) {
                            return $nBlock->name == $item['name'];
                        }));

                        if (sizeof($block)) {
                            $item['content'] = $block[0]->content;
                        }

                        if ($item['page_content_type_id'] == 9 && is_array($item['content'])) {
                            $item['content'] = array_map(function($content) use ($item) {
                                $newContent = [];
                                foreach ($content as $key => $value) {
                                    $contentField = array_values(array_filter($item['fields'], function ($cField) use ($key) {
                                        return $cField['field'] === $key;
                                    }));

                                    if (sizeof($contentField)) {
                                        $type = $contentField[0]['page_content_type_id'];
                                        $width = $contentField[0]['width'] ?? null;
                                        $height = $contentField[0]['height'] ?? null;
                                        if ($type == 4) {
                                            $newValue = [
                                                'id' => $value,
                                                'fileAlt' => '',
                                                'alt' => '',
                                                'width' => $width,
                                                'height' => $height,
                                                'model' => [
                                                    'name' => get_class($this),
                                                    'alts' => [],
                                                ],
                                            ];
                                            $value = $newValue;
                                        }

                                        $newContentField = [
                                            'page_content_type_id' => $type,
                                            'key' => uniqid(),
                                            'id' => uniqid(),
                                            'content' => $value,
                                        ];
                                        $newContent[$key] = $newContentField;
                                    }
                                }

                                return $newContent;

                            }, $item['content']);
                            if (isset($item['fields']) && is_array($item['fields'])) {
                                $item['fields'] = array_map(function($field) {
                                    $field['id'] = uniqid();
                                    $field['key'] = uniqid();
                                    return $field;
                                }, $item['fields']);
                            }
                        }

                        if ($item['page_content_type_id'] == 4) {
                            $blockAsObject = (object) $block[0];
                            $width = $blockAsObject->width ?? null;
                            $height = $blockAsObject->height ?? null;
                            if (!$width && isset($item['width'])) {
                                $width = $item['width'];
                            }
                            if (!$height && isset($item['height'])) {
                                $height = $item['height'];
                            }
                            $newValue = [
                                'id' => $item['content'],
                                'fileAlt' => '',
                                'alt' => '',
                                'width' => $width,
                                'height' => $height,
                                'model' => [
                                    'name' => get_class($this),
                                    'alts' => [],
                                ],
                            ];
                            $item['content'] = $newValue;
                        }

                        $item['id'] = uniqid();
                        $item['key'] = uniqid();
                        return $item;
                    }, $newField['fields']);
                }

                $formattedData[] = $newField;
            }
        }

        // attach the alts
        $dots = array_dot($formattedData);
        if (sizeof($imageAltsLookup)) {
            foreach ($imageAltsLookup as $key => $value) {
                if (isset($dots[$key.'.alt'])) {
                    $dots[$key.'.alt'] = $value;
                }
            }

            $formattedDataWithAlts = [];
            foreach ($dots as $key => $value) {
                array_set($formattedDataWithAlts, $key, $value);
            }
            $formattedData = $formattedDataWithAlts;
        }

        // help()->trace($formattedData);

        return $formattedData;
    }

}
