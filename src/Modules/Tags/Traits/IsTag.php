<?php

namespace RefinedDigital\CMS\Modules\Tags\Traits;

use RefinedDigital\CMS\Modules\Core\Models\Uri;
use RefinedDigital\CMS\Modules\Tags\Models\Tag;
use RefinedDigital\CMS\Modules\Tags\Scopes\IsTagScope;

trait IsTag
{

    public $isTag = true;

    /**
     * Boot the is page trait for a model.
     *
     * @return void
     */
    public static function bootIsTag()
    {
        static::addGlobalScope(new IsTagScope);

        static::saved(function($model) {

            $template = $model->templateId ?: 1;

            $modelType = get_class($model);

            $uriData = [
                'title'         => $model->name,
                'name'          => $model->name,
                'description'   => '',
                'template_id'   => $template,
                'uriable_id'    => $model->id,
                'uriable_type'  => $modelType,
            ];

            if ($model->meta) {
                $model->meta->fill($uriData);
                $model->meta->save();
            } else {
                Uri::create($uriData);
            }
        });

    }


    public function meta()
    {
        return $this->morphOne('RefinedDigital\CMS\Modules\Core\Models\Uri', 'uriable');
    }


    public static function findOrCreate($values, string $type = null)
    {
        $tags = collect($values)->map(function ($value) use ($type) {
            if ($value instanceof Tag) {
                return $value;
            }

            return static::findOrCreateFromString($value, $type);
        });

        return is_string($values) ? $tags->first() : $tags;
    }

    public static function findFromString(string $name, string $type = null)
    {

        $query = static::query()
            ->where('name', $name)
            ->where('type', $type)
            ->first();

        return $query;
    }

    protected static function findOrCreateFromString(string $name, string $type = null): Tag
    {
        $tag = static::findFromString($name, $type);

        if (! $tag) {
            $tag = static::create([
                'name' => $name,
                'type' => $type,
            ]);
        }

        return $tag;
    }
}