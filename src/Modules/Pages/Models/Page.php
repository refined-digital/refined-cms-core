<?php

namespace RefinedDigital\CMS\Modules\Pages\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Core\Traits\ClearResponseCacheTrait;
use RefinedDigital\CMS\Modules\Core\Traits\HasSettings;
use RefinedDigital\CMS\Modules\Pages\Traits\ContentBySource;
use RefinedDigital\CMS\Modules\Pages\Traits\IsPage;
use RefinedDigital\CMS\Modules\Pages\Traits\SortablePageTrait;
use Spatie\EloquentSortable\Sortable;

class Page extends CoreModel implements Sortable
{
    use SoftDeletes;
    use IsPage;
    use SortablePageTrait;
    use ClearResponseCacheTrait;
    use ContentBySource;
    use HasSettings;

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
        $setup = pages()->formatConfigContent(config('pages.content'));

        $setupLookup = [];
        $setupLookupByName = [];
        if ($setup && sizeof($setup)) {
            foreach ($setup as $field) {
                $setupLookup[$field['template']] = $field;
                $setupLookupByName[$field['name']] = $field;
            }
        }

        $formattedData = [];
        if ($data && sizeof($data)) {
            foreach ($data as $field) {
                if (isset($field->template)) {
                    $lookup = $setupLookup;
                    $lookupKey = $field->template;
                } else {
                    $lookup = $setupLookupByName;
                    $lookupKey = $field->name;
                }

                if (!isset($lookup[$lookupKey])) {
                    continue;
                }

                $newField = $lookup[$lookupKey];
                $newField['id'] = uniqid();
                $newField['key'] = uniqid();
                if (isset($newField['fields'], $field->fields) && sizeof($newField['fields']) && sizeof($field->fields)) {
                    $newField['fields'] = array_map(function($item) use ($field) {
                        $block = array_values(array_filter($field->fields, function ($nBlock) use($item) {
                            return $nBlock->name == $item['name'];
                        }));

                        if (sizeof($block)) {
                            $item['content'] = $block[0]->content ?? '';
                        }

                        if (!isset($item['content'])) {
                            $item['content'] = $item['page_content_type_id'] == 9 ? [] : '';
                        }

                        if ($item['page_content_type_id'] == 9 && is_array($item['content'])) {
                            $item['content'] = array_map(function($content) use ($item) {
                                $newContent = [];
                                // add any new fields that might be missing to the content object
                                foreach ($item['fields'] as $field) {
                                    $fKey = $field['field'] ?? \Str::snake($field['name']);
                                    if (!isset($content->{$fKey})) {
                                        $content->{$fKey} = null;
                                    }
                                }

                                foreach ($content as $key => $value) {
                                    $contentField = array_values(array_filter($item['fields'], function ($cField) use ($key) {
                                        $cKey = $cField['field'] ?? \Str::snake($cField['name']);
                                        return $cKey === $key;
                                    }));

                                    $newContentField = [
                                        'page_content_type_id' => 1,
                                        'key' => uniqid(),
                                        'id' => uniqid(),
                                        'content' => $value,
                                        'show' => true
                                    ];

                                    if (sizeof($contentField)) {
                                        $newContentField['page_content_type_id'] = $contentField[0]['page_content_type_id'] ?? 1;
                                        if ($newContentField['page_content_type_id'] === 6 && isset($contentField[0]['options'])) {
                                            $newContentField['options'] = $contentField[0]['options'];
                                        }
                                    }

                                    $newContent[$key] = $newContentField;
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

                        $item['id'] = uniqid();
                        $item['key'] = uniqid();
                        return $item;
                    }, $newField['fields']);
                }

                $formattedData[] = $newField;
            }
        }

        return $formattedData;
    }
}
