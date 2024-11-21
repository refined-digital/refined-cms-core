<?php

namespace RefinedDigital\CMS\Modules\Tags\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use RefinedDigital\CMS\Modules\Tags\Models\Tag;
use Str;

trait Taggable
{

    public static function getTagClassName(): string
    {
        return Tag::class;
    }

    /**
     * Boot the is page trait for a model.
     *
     * @return void
     */
    public static function bootTaggable()
    {
        static::saved(function($model) {

            if (request()->has('modelTags') && is_array(request()->get('modelTags')) && sizeof(request()->get('modelTags'))) {
                // first remove the tags
                $model->syncTags([]);


                // now add the tags
                foreach (request()->get('modelTags') as $type => $tags) {
                    $tags = array_filter(explode(',', $tags));
                    if (sizeof($tags)) {
                        foreach ($tags as $tag) {

                            $tagToAdd = Tag::findOrCreate(trim($tag), $type);
                            $model->attachTag($tagToAdd);

                        }
                    }
                }
            }

        });

        static::getModel()->deleted(function($model) {

            if(!$model->implementsTagSoftDeletes()) {
                // remove tags if we are force deleting
                $model->syncTags([]);
            }
        });

    }

    public function implementsTagSoftDeletes()
    {
        return method_exists($this, 'runSoftDelete');
    }


    public function taggables(): MorphToMany
    {
        return $this
            ->morphToMany(self::getTagClassName(), 'taggable')
            ->orderBy('position')
        ;
    }

    /**
     * @param array|\ArrayAccess|Tag $tags
     *
     * @return $this
     */
    public function attachTags($tags)
    {
        $className = static::getTagClassName();

        $tags = collect($className::findOrCreate($tags));

        $this->taggables()->syncWithoutDetaching($tags->pluck('id')->toArray());

        return $this;
    }

    /**
     * @param string|Tag $tag
     *
     * @return $this
     */
    public function attachTag($tag)
    {
        return $this->attachTags([$tag]);
    }

    /**
     * @param array|\ArrayAccess $tags
     *
     * @return $this
     */
    public function detachTags($tags)
    {
        $tags = static::convertToTags($tags);

        collect($tags)
            ->filter()
            ->each(function (Tag $tag) {
                $this->taggables()->detach($tag);
            });

        return $this;
    }

    /**
     * @param string|Tag $tag
     *
     * @return $this
     */
    public function detachTag($tag)
    {
        return $this->detachTags([$tag]);
    }

    /**
     * @param array|\ArrayAccess $tags
     *
     * @return $this
     */
    public function syncTags($tags)
    {
        $className = static::getTagClassName();

        $tags = collect($className::findOrCreate($tags));

        $this->taggables()->sync($tags->pluck('id')->toArray());

        return $this;
    }



    protected function getArrayableItems(array $values)
    {
        $appends = ['tags', 'categories', 'modelTags'];
        foreach ($appends as $field) {
            if (!in_array($field, $this->appends)){
                $this->appends[] = $field;
            }
        }

        return parent::getArrayableItems($values);
    }

    public function getTagsAttribute()
    {
        return $this->getTagCollection('tags');
    }

    public function getCategoriesAttribute()
    {
        return $this->getTagCollection('categories');
    }

    public function getModelTagsAttribute()
    {
        $data = [];
        $tags = $this->taggables;
        if (sizeof($tags)) {
            foreach ($tags as $tag) {
                if (!isset($data[$tag->type])) {
                    $data[$tag->type] = [];
                }
                $data[$tag->type][] = [
                    'id' => $tag->id,
                    'name' => $tag->name
                ];
            }
        }

        return $data;
    }

    private function getTagCollection($type)
    {
        $tags = $this->taggables;
        $newTags = collect([]);
        if (sizeof($tags)) {
            foreach ($tags as $tag) {
                if ($tag->type == $type) {
                    if (!isset($tag->meta->original_uri)) {
                        $tag->meta->original_uri = $tag->meta->uri;
                        $tag->meta->uri = Str::slug($type).'/'.$tag->meta->uri;
                    }
                    $newTags->push($tag);
                }
            }
        }

        return $newTags;
    }


    public function scopeAllWithTags(Builder $query, $tags, $type = null): Builder
    {
        $tags = static::convertToTags($tags, $type);

        collect($tags)->each(function ($tag) use ($query) {
            $query->whereHas('taggables', function (Builder $query) use ($tag) {
                return $query->where('id', $tag ? $tag->id : 0);
            });
        });

        return $query;

    }

    protected static function convertToTags($values, $type = null)
    {
        $collect = collect($values)->map(function ($value) use ($type) {
            if ($value instanceof Tag) {
                if (isset($type) && $value->type != $type) {
                    throw new InvalidArgumentException("Type was set to {$type} but tag is of type {$value->type}");
                }

                return $value;
            }

            $className = static::getTagClassName();

            return $className::findFromString($value, $type);
        });

        return $collect;
    }
}
