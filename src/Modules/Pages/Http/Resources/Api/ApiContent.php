<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RefinedDigital\CMS\Modules\Content\Models\Content;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

/**
 * Builds the headless api blocks for a record with content blocks.
 *
 * The api (used by the Shopify Hydrogen front ends) predates the contents table, so each
 * block is rebuilt into the old config shape, {name, template, fields: [{name, field,
 * page_content_type_id, content}]}, and every field becomes {content, type}. A block's
 * apiResource receives that same shape, plus the record as `model`.
 */
class ApiContent
{
    private const string SETTING_KEY = '[settings:';

    /**
     * @return array<int, object{template: string, content: object}>
     */
    public static function forModel(Model $model): array
    {
        if (! method_exists($model, 'groupContentRows')) {
            return [];
        }

        $rows = Content::select(['content_class', 'field', 'data', 'position'])
            ->whereContentableId($model->id)
            ->whereContentableType($model::class)
            ->orderBy('position')
            ->get();

        $blocks = [];
        foreach ($model->groupContentRows($rows) as $types) {
            foreach ($types as $class => $fieldRows) {
                if (! class_exists($class)) {
                    continue;
                }

                $block = new $class();
                $legacy = [
                    'name' => $block->getName(),
                    'template' => $block->getApiTemplate(),
                    'fields' => self::fieldsWithContent($block->getFields(), $fieldRows),
                    'model' => $model,
                ];

                $resource = $block->getApiResource();
                $content = $resource
                    ? (new $resource($legacy))->toArray(request())
                    : self::formatFields($legacy['fields']);

                $blocks[] = (object) [
                    'template' => $legacy['template'],
                    // the json round trip turns resource arrays into the objects PageContentItemResource reads
                    'content' => json_decode(json_encode($content)),
                ];
            }
        }

        return $blocks;
    }

    /**
     * Attaches each stored value to its field definition, defaulting like the old page content did.
     */
    private static function fieldsWithContent(array $fields, iterable $rows): array
    {
        $values = [];
        foreach ($rows as $row) {
            $values[Str::snake($row->field)] = $row->data['content'] ?? null;
        }

        return array_map(function (array $field) use ($values) {
            $isRepeatable = (int) $field['page_content_type_id'] === PageContentType::REPEATABLE->value;
            $content = $values[$field['field']] ?? null;
            $field['content'] = $isRepeatable ? (is_array($content) ? $content : []) : ($content ?? '');

            return $field;
        }, $fields);
    }

    /**
     * Port of the old PageRepository::formatPageContentFields() for api requests.
     */
    private static function formatFields(array $fields): object
    {
        $formatted = new \stdClass();

        foreach ($fields as $field) {
            $type = (int) $field['page_content_type_id'];
            $content = $field['content'];

            if ($type === PageContentType::REPEATABLE->value) {
                $content = array_map(function ($row) use ($field) {
                    $row = (array) $row;
                    $item = new \stdClass();
                    foreach ($field['fields'] as $subField) {
                        $subType = (int) $subField['page_content_type_id'];
                        $item->{$subField['field']} = (object) [
                            'content' => self::formatValue($row[$subField['field']] ?? null, $subType, $subField),
                            'type' => $subType,
                        ];
                    }

                    return $item;
                }, $content);
            } else {
                $content = self::formatValue($content, $type, $field);
            }

            $formatted->{Str::slug($field['name'], '_')} = (object) [
                'content' => $content,
                'type' => $type,
            ];
        }

        return self::replaceSettings($formatted);
    }

    private static function formatValue(mixed $value, int $type, array $field): mixed
    {
        if ($type === PageContentType::IMAGE->value) {
            return (object) [
                'id' => $value,
                'width' => $field['width'] ?? null,
                'height' => $field['height'] ?? null,
            ];
        }

        // the admin saves links as {type, url, text, ...}, the api has always sent the plain url
        if ($type === PageContentType::LINK->value && (is_array($value) || is_object($value))) {
            return ((array) $value)['url'] ?? null;
        }

        return $value;
    }

    /**
     * Swaps `[settings:key]` placeholders for the setting's value, as the old api did.
     */
    private static function replaceSettings(object $fields): object
    {
        $flattened = Arr::dot(json_decode(json_encode($fields), true));
        $keys = array_values(array_unique(array_filter(
            $flattened,
            fn ($value) => is_string($value) && str_contains($value, self::SETTING_KEY)
        )));

        if (! $keys) {
            return $fields;
        }

        $values = settings()->getByKeyCodes($keys);
        foreach ($flattened as $key => $value) {
            if (is_string($value) && isset($values[$value])) {
                $flattened[$key] = $values[$value];
            }
        }

        return json_decode(json_encode(Arr::undot($flattened)));
    }
}
