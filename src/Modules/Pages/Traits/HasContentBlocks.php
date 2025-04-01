<?php

namespace RefinedDigital\CMS\Modules\Pages\Traits;

use Illuminate\Http\Request;
use RefinedDigital\CMS\Modules\Content\Models\Content;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

trait HasContentBlocks
{
    public static function bootHasContentBlocks(): void
    {
        static::saved(function ($model) {

            // first delete all content that matches the class and the id
            Content::whereContentableType($model::class)
                ->whereContentableId($model->id)
                ->delete();

            // now loop over the data and create the content
            $content = request()->has('page')
                ? request()->input('page.content')
                : request()->input('content');

            if (\Str::isJson($content) && gettype($content) === 'string') {
                $content = json_decode($content, true);
            }

            if ($content && is_array($content) && sizeof($content)) {
                foreach ($content as $index => $type) {
                    if (isset($type['fields']) && is_array($type['fields']) && sizeof($type['fields'])) {
                        foreach ($type['fields'] as $field) {
                            if (!isset($field['content'])) {
                                continue;
                            }

                            $data = $field['content'];

                            if ((int) $field['page_content_type_id'] === PageContentType::REPEATABLE->value) {
                                if (is_array($data) && sizeof($data)) {
                                    $data = array_map(function($item) {
                                        $fieldData = new \stdClass();

                                        foreach ($item as $key => $value) {
                                            $fieldData->{$key} = $value['content'] ?? null;
                                        }

                                        return $fieldData;
                                    }, $data);
                                } else {
                                    $data = [];
                                }
                            }


                            $createData = [
                                'position' => $index,
                                'contentable_id' => $model->id,
                                'contentable_type' => $model::class,
                                'content_class' => $type['class'],
                                'field' => $field['name'],
                                'data' => [
                                    'content' => $data
                                ]
                            ];

                            Content::create($createData);
                        }
                    }
                }
            }
        });
    }

    protected function initializeHasContentBlocks(): void
    {
        $this->addToAppends([
            'content'
        ]);
    }

    public function getContentAttribute(): string | array
    {
        $content = Content::select(['content_class', 'field', 'data', 'position'])
            ->whereContentableId($this->id)
            ->whereContentableType(self::class)
            ->orderBy('position')
            ->get();

        $data = [];
        foreach ($content as $item) {
            $key = $item->position;
            $key2 = $item->content_class;
            if (!isset($data[$key])){
                $data[$key] = [];
            }

            if (!isset($data[$key][$key2])) {
                $data[$key][$key2] = [];
            }

            $data[$key][$key2][] = $item;
        }

        if (\Str::contains(request()->route()->getName(), 'refined.')) {
            return $this->formatForAdmin($data);
        }

        return $this->formatForFE($data);
    }

    private function formatForAdmin($data): array
    {
        $content = [];

        foreach ($data as $position => $blocks) {
            foreach ($blocks as $type => $fields) {
                $class = new $type();
                $item = $class->getForConfig($type);

                $formattedContent = $this->formatContent($class->getFields(), $fields, true);

                foreach ($item['fields'] as $key => $field) {
                    $item['fields'][$key]['content'] = $formattedContent->{$field['field']} ?? null;
                    $pageContentTypeId = (int) $field['page_content_type_id'];

                    if (
                        $pageContentTypeId === PageContentType::SELECT->value &&
                        isset($field['options']) &&
                        $field['options'] === 'forms' &&
                        function_exists('forms')
                    ) {
                        $item['fields'][$key]['options'] = forms()->getForSelect('content forms');
                    }

                    if ($pageContentTypeId === PageContentType::REPEATABLE->value) {
                        $lookup = [];
                        foreach ($field['fields'] as $f) {
                            $lookup[$f['field']] = $f;
                        }

                        $item['fields'][$key]['content'] = array_map(function ($item) use ($lookup) {
                            $newContent = $lookup;
                            foreach ($item as $key => $value) {
                                if (!isset($newContent[$key])) {
                                    continue;
                                }
                                $newContent[$key]['content'] = $value;
                            }

                            return $newContent;
                        }, $item['fields'][$key]['content']);

                        $item['fields'][$key]['content'] = array_map(function ($item) {
                            foreach ($item as $key => $value) {
                                $item[$key]['show'] =  true;
                                if (!isset($item[$key]['content'])) {
                                    $item[$key]['content'] = '';
                                }
                                unset($item[$key]['field']);
                            }
                            return $item;
                        }, $item['fields'][$key]['content']);
                    }
                }

                $content[] = $item;
            }
        }

        return $content;

    }

    private function formatForFE($data): string
    {
        $html = '';
        $index = 0;
        foreach ($data as $position => $blocks) {
            foreach ($blocks as $type => $fields) {
                $class = new $type();
                $content = $this->formatContent($class->getFields(), $fields);
                $template = $class->getTemplate();

                if (view()->exists($template)) {
                    $classes = [
                        'page__block',
                        'page__block--'.\Str::kebab($class->getName()),
                    ];
                    $html .= view()
                        ->make($template)
                        ->with(compact('index'))
                        ->with(compact('classes'))
                        ->with('page', $this)
                        ->with('content', $content);
                } else {
                    $html .= '<p style="color:#f00">Template "'.$template.'" does not exist</p>';
                }

                $index ++;

            }

        }

        return $html;
    }

    private function formatContent($fields, $content, $admin = false): object
    {
        $data = new \stdClass();

        foreach ($content as $field) {
            $key = \Str::snake($field->field);
            $data->{$key} = $field->data['content'] ?? null;
        }

        foreach ($fields as $field) {
            $key = \Str::snake($field['field']);

            if (!isset($data->{$key})) {
                $data->{$key} = null;
            }

            $pageContentTypeId = (int) $field['page_content_type_id'];

            if ($pageContentTypeId === PageContentType::IMAGE->value && !$admin) {
                $value = new \stdClass();
                $value->width = $field['width'] ?? null;
                $value->height = $field['height'] ?? null;
                $value->id = $data->{$key};
                $data->{$key} = $value;
            }

            if ($pageContentTypeId === PageContentType::REPEATABLE->value && !$admin) {
                $value = $data->{$key};
                $lookup = [];

                foreach ($field['fields'] as $fd) {
                    $lookup[$fd['field']] = $fd;
                }

                if (is_array($value) && sizeof($value)) {
                    foreach ($value as $index => $valueFields) {
                        foreach ($valueFields as $k => $v) {
                            $lookupField = $lookup[$k];
                            if ((int) $lookupField['page_content_type_id'] === PageContentType::IMAGE->value) {
                                $itemValue = new \stdClass();
                                $itemValue->id = $v;
                                $itemValue->width = $lookupField['width'] ?? null;
                                $itemValue->height = $lookupField['height'] ?? null;
                                $value[$index][$k] = $itemValue;
                            }
                        }
                    }

                    $data->{$key} = array_map(fn ($item) => (object) $item, $value);
                }
            }
        }

        return $data;
    }

}
