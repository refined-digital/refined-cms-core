<?php

namespace RefinedDigital\CMS\Modules\Content;

use RefinedDigital\CMS\Modules\Core\Aggregates\ContentAggregate;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class BaseContent
{
    public function getField(string $name, array $attributes = []): array|null
    {
        $agg = app(ContentAggregate::class);
        $fields = $agg->getNewFields();

        if (isset($fields[$name])) {
            $field = $fields[$name];

            if ($attributes) {
                $field = array_merge($field, $attributes);
            }

            return $field;
        }

        return null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTemplate(): string
    {
        return 'content::'.str_replace(' ', '', $this->name).'.Views.'.\Str::kebab($this->name);
    }

    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    public function getForConfig(string $class): array
    {
        $config = [
            'name' => $this->getName(),
            'class' => $class,
        ];

        $description = $this->getDescription();
        if ($description) {
            $config['description'] = $description;
        }

        $config['fields'] = $this->getFields();

        return $config;
    }

    public function getSettings(): array
    {
        if (isset($this->settings) && $this->settings) {
            return $this->settings;
        }

        return [];
    }

    public function getFields(): array
    {
        $fields = array_filter($this->fields());

        $fields = $this->formatFields($fields);

        return $fields;
    }

    private function formatFields(array $fields)
    {
        return array_map(function($field) {
            $data = [
                ...$field,
                'field' => \Str::snake($field['name']),
            ];

            if (
                (int) $data['page_content_type_id'] === PageContentType::SELECT->value &&
                isset($data['options']) &&
                $data['options'] === 'forms' &&
                function_exists('forms')
            ) {
                $data['options'] = forms()->getForSelect('content forms');
            }

            if (isset($field['fields'])) {
                $data['fields'] = $this->formatFields($field['fields']);
            }

            return $data;
        }, $fields);
    }
}

