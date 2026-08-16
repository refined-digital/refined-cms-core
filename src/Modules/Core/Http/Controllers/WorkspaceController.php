<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleAggregate;
use RefinedDigital\CMS\Modules\Pages\Traits\HasContentBlocks;
use RefinedDigital\CMS\Modules\Pages\Traits\RendersBlockPreview;

/**
 * Generic endpoints for the full-screen module workspaces (blog etc.).
 * {module} url segments only resolve to model classes that modules registered
 * through CustomModuleAggregate and that use HasContentBlocks.
 */
class WorkspaceController extends Controller
{
    use RendersBlockPreview;

    private function resolveModel(string $module): string
    {
        $model = app(CustomModuleAggregate::class)->getModel($module);

        if (
            !$model
            || !class_exists($model)
            || !in_array(HasContentBlocks::class, class_uses_recursive($model))
        ) {
            abort(404);
        }

        return $model;
    }

    /**
     * Lightweight listing for the workspace rail: every record, minimal fields.
     */
    public function list($module)
    {
        $model = $this->resolveModel($module);

        $items = $model::order()->get()->map(function ($record) {
            $item = [
                'id' => $record->id,
                'name' => $record->name,
                'active' => (int) $record->active,
            ];

            if (!empty($record->published_at)) {
                $date = $record->published_at instanceof \DateTimeInterface
                    ? $record->published_at
                    : \Illuminate\Support\Carbon::parse($record->published_at);
                $item['date'] = $date->format('d/m/Y');
            }

            return $item;
        });

        return response()->json(['success' => 1, 'items' => $items]);
    }

    /**
     * Everything the workspace needs to edit one record: the form schema,
     * hydrated field values (with repeatable rows healed against the current
     * definitions), the admin-shaped content blocks, meta and tags.
     */
    public function data($module, $id)
    {
        $model = $this->resolveModel($module);
        // 'new' returns a blank record's schema and defaults for the add flow
        $record = $id === 'new' ? new $model() : $model::findOrFail($id);

        $schema = $record->getFormFields();

        $basePage = app(CustomModuleAggregate::class)->getSitemapBasePage(ltrim($model, '\\'));

        $values = $this->hydrateValues($record, $schema);

        if ($id === 'new') {
            $values = $this->applyNewRecordDefaults($values, $schema);
        }

        return response()->json([
            'success' => 1,
            'id' => $record->id,
            'url' => rtrim(help()->getPublicUrl(), '/').'/'.trim($basePage ?? '', '/').'/'.($record->meta->uri ?? ''),
            'schema' => $schema,
            'values' => $values,
            'content' => is_array($record->content) ? $record->content : [],
            'meta' => [
                'uri' => $record->meta->uri ?? '',
                'title' => $record->meta->title ?? '',
                'description' => $record->meta->description ?? '',
            ],
            'modelTags' => $record->modelTags ?? new \stdClass(),
        ]);
    }

    public function preview($module, $id)
    {
        $model = $this->resolveModel($module);

        return $this->renderPreviewPage($model::findOrFail($id));
    }

    public function previewRender($module, $id)
    {
        try {
            $model = $this->resolveModel($module);
            $record = $model::findOrFail($id);
            $html = $this->renderDraftBlocks($record, request()->input('content', []));

            return response()->json(['success' => 1, 'html' => $html]);
        } catch (\Throwable $e) {
            // a half-typed value blowing up a blade must not break the builder
            return response()->json(['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Walks the form schema and pulls each field's current value off the
     * record, keyed by the field's posted name.
     */
    private function hydrateValues($record, $schema): array
    {
        $values = [];

        foreach ($this->schemaFields($schema) as $field) {
            $name = $field->name ?? null;
            $type = $field->type ?? 'text';

            if (!$name || Str::startsWith($name, 'meta[')) {
                continue;
            }

            // handled through their own channels
            if (in_array($type, ['contentBlocks', 'tags', 'readonly', 'className', 'comment'])) {
                continue;
            }

            if ($type === 'repeatable') {
                $values[$name] = $this->mergeRepeatableRows(
                    $this->decodeRepeatable($record->getRawOriginal($name) ?? $record->{$name}),
                    json_decode(json_encode($field->fields ?? []), true)
                );
                continue;
            }

            if (Str::contains($name, 'data__')) {
                $key = str_replace('data__', '', $name);
                $values[$name] = $record->data->{$key} ?? null;
                continue;
            }

            $value = $record->{$name} ?? null;

            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $values[$name] = $value;
        }

        return $values;
    }

    /**
     * A blank model has no attribute defaults, so give a brand new record
     * usable ones: selects take their first option, dates take now.
     */
    private function applyNewRecordDefaults(array $values, $schema): array
    {
        foreach ($this->schemaFields($schema) as $field) {
            $name = $field->name ?? null;
            if (!$name || isset($values[$name])) {
                continue;
            }

            $type = $field->type ?? 'text';

            if ($type === 'select' && !empty($field->options)) {
                $options = json_decode(json_encode($field->options), true);
                $first = reset($options);
                $values[$name] = is_array($first) ? ($first['value'] ?? null) : array_key_first($options);
                continue;
            }

            if ($type === 'datetime') {
                $values[$name] = now()->format('Y-m-d H:i:s');
            } elseif ($type === 'date') {
                $values[$name] = now()->format('Y-m-d');
            }
        }

        return $values;
    }

    /**
     * Flattens every schema tab shape (sections/blocks/fields) into one list.
     */
    private function schemaFields($schema): array
    {
        $fields = [];

        $fromBlocks = function ($blocks) use (&$fields) {
            foreach ($blocks as $block) {
                foreach ($block->fields ?? [] as $row) {
                    foreach ((array) $row as $field) {
                        $fields[] = $field;
                    }
                }
            }
        };

        foreach ($schema as $tab) {
            if (isset($tab->sections)) {
                foreach ((array) $tab->sections as $section) {
                    $fromBlocks($section->blocks ?? []);
                }
            }
            if (isset($tab->blocks)) {
                $fromBlocks($tab->blocks);
            }
            if (isset($tab->fields)) {
                foreach ($tab->fields as $row) {
                    foreach ((array) $row as $field) {
                        $fields[] = $field;
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Model repeatable values are stored json (sometimes double-encoded, see
     * CoreModel::getModelImagesAttribute) - normalise to an array of rows.
     */
    private function decodeRepeatable($value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (is_object($value)) {
            $value = json_decode(json_encode($value), true);
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Rebuilds every saved row against the current field definitions so
     * newly-added fields appear on existing rows (key-based - a row missing a
     * defined key gets it added with empty content; definition metadata like
     * notes is refreshed; keys no longer defined are dropped).
     */
    private function mergeRepeatableRows(array $rows, array $defs): array
    {
        $merged = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $newRow = [];
            foreach ($defs as $def) {
                $key = $def['field'] ?? Str::snake($def['name'] ?? '');
                if (!$key) {
                    continue;
                }

                $cell = isset($row[$key]) && is_array($row[$key]) ? $row[$key] : [];

                $newRow[$key] = array_merge($def, [
                    'content' => $cell['content'] ?? '',
                ]);

                foreach (['content_colour', '_key', 'id', 'key'] as $keep) {
                    if (isset($cell[$keep])) {
                        $newRow[$key][$keep] = $cell[$keep];
                    }
                }
            }

            if (isset($row['_key'])) {
                $newRow['_key'] = $row['_key'];
            }

            $merged[] = $newRow;
        }

        return $merged;
    }
}
