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
        'data' => 'object'
    ];


    public function content()
    {
        return $this->hasMany('RefinedDigital\CMS\Modules\Pages\Models\PageContent')
                ->orderBy('position','asc')
                ->select('id', 'name', 'page_id', 'page_content_type_id', 'note', 'source', 'content', 'position');
    }

}
