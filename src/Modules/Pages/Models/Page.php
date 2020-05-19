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
        'page_type', 'form_id', 'position', 'name', 'banner', 'data'
    ];

    protected $appends = [ 'banner_image' ];

    protected $casts = [
        'data' => 'object'
    ];


    public function content()
    {
        return $this->hasMany('RefinedDigital\CMS\Modules\Pages\Models\PageContent')
                ->orderBy('position','asc')
                ->select('id', 'name', 'page_id', 'page_content_type_id', 'note', 'source', 'content', 'position');
    }

    public function getContentBySource($source)
    {
        if(isset($this->content) && sizeof($this->content)) {
            foreach($this->content as $content) {
                if($content->source == $source) {
                    return help()->formatOEmbed($content->content);
                }
            }
        }
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
