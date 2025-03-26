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
                // todo: module contents
                : [];

            if ($content && is_array($content) && sizeof($content)) {
                foreach ($content as $index => $type) {
                    if (isset($type['fields']) && is_array($type['fields']) && sizeof($type['fields'])) {
                        foreach ($type['fields'] as $field) {
                            if (!isset($field['content'])) {
                                continue;
                            }

                            $data = $field['content'];

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

    protected function initializeHasContentBlocks()
    {
        $this->addToAppends([
            'content'
        ]);
    }

    public function getContentAttribute()
    {
        $content = Content::select(['content_class', 'field', 'data', 'position'])
            ->whereContentableId($this->id)
            ->whereContentableType(self::class)
            ->orderBy('position')
            ->orderBy('content_class')
            ->get();

        $data = [];
        foreach ($content as $item) {
            $key = $item->content_class;
            $key2 = $item->position;
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

    private function formatForAdmin($data)
    {
        $content = [];

        foreach ($data as $type => $blocks) {
            $class = new $type();
            foreach ($blocks as $index => $fields) {
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
        foreach ($data as $type => $blocks) {
            $class = new $type();
            foreach ($blocks as $fields) {
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
                        ->with('content', $content);
                } else {
                    $html .= '<p style="color:#f00">Template "'.$template.'" does not exist</p>';
                }

                $index ++;

            }

        }

        return $html;
    }

    private function formatContent($fields, $content, $admin = false)
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
        }

        return $data;
    }

}
