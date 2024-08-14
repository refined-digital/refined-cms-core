<?php

namespace RefinedDigital\CMS\Modules\Pages\Traits;

use RefinedDigital\CMS\Modules\Core\Models\Uri;
use RefinedDigital\CMS\Modules\Pages\Scopes\IsPageScope;
use Str;

trait IsPage
{

    public $isPage = true;

    /**
     * Boot the is page trait for a model.
     *
     * @return void
     */
    public static function bootIsPage()
    {
        static::addGlobalScope(new IsPageScope);

        static::saving(function($model) {
            if (class_basename($model) == 'Page' && isset($model->data)) {
                if (is_array($model->data) && sizeof($model->data) < 1) {
                    $model->data = null;
                }
            }
        });

        static::saved(function($model) {

            // set the title
            $title = null;

            if (request()->has('page.meta')) {
                $request = request()->input('page');
            } else {
                $request = request()->all();
            }

            if(isset($request['meta']['title'])) {
                $title = $request['meta']['title'];
            }
            if (!$title && isset($request['name'])) {
                $title = $request['name'];
            }
            if (!$title && isset($model->meta->title)) {
                $title = $model->meta->title;
            }
            if (!$title && isset($model->name)) {
                $title = $model->name;
            }


            // set the description
            $description = null;
            if(isset($request['meta']['description'])) {
                $description = $request['meta']['description'];
            }

            $template = $model->templateId ?: 1;
            $base = class_basename($model);
            if ($base != 'Page') {
                $config = config(strtolower($base));
                if (!$config) {
                    $config = config(Str::plural(strtolower($base)));
                }

                if (isset($config['details_template_id'])) {
                    $template = $config['details_template_id'];
                }
            } else {
                if (isset($request['meta']['template_id']) && $request['meta']['template_id']) {
                    $template = $request['meta']['template_id'];
                }
            }

            $name = '';
            if (isset($request['name'])) {
                $name = $request['name'];
            }

            if (!$name) {
                $name = $model->name;
            }

            $search = ['[colour]', '[/colour]'];
            $replace = ['', ''];
            $name = str_replace($search, $replace, $name);

            $modelType = get_class($model);

            $uriData = [
                'title'         => $title,
                'name'          => $name,
                'description'   => $description,
                'template_id'   => $template,
                'uriable_id'    => $model->id,
                'uriable_type'  => $modelType,
            ];

            if ($model->meta) {
                $model->meta->fill($uriData);
                $model->meta->save();
                if ($base == 'Page' && $model->meta->uriable_id == 1) {
                    $model->meta->uri = '/';
                    $model->meta->save();
                }
            } else {
                $meta = Uri::create($uriData);
                if ($base == 'Page' && $meta->uriable_id == 1) {
                    $meta->uri = '/';
                    $meta->save();
                }
            }
        });

        /*
        static::getModel()->deleted(function($model) {

           if($model->implementsSoftDeletes()) {
                // do the soft deleting
                //$model->meta->delete();
                if ($model->content) {
                    $model->content()->delete();
                }
            } else {
                // do the hard deleting
                //$model->meta->forceDelete();
                if ($model->content) {
                    $model->content()->forceDelete();
                }
            }
        });*/

    }


    public function implementsSoftDeletes()
    {
        return method_exists($this, 'runSoftDelete');
    }


    public function meta()
    {
        return $this->morphOne('RefinedDigital\CMS\Modules\Core\Models\Uri', 'uriable');
    }

    public function getBannerImageAttribute()
    {
        if (is_numeric($this->attributes['banner'])) {
            $image = image()
                        ->load($this->attributes['banner'])
                        ->width(config('pages.banner.internal.width'))
                        ->height(config('pages.banner.internal.height'))
                        ->fill()
                        ->save();
            return $image;
        }

        return null;
    }
}
