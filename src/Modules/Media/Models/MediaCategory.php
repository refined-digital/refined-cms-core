<?php

namespace RefinedDigital\CMS\Modules\Media\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Media\Traits\SortableMediaCategoryTrait;
use Spatie\EloquentSortable\Sortable;

class MediaCategory extends CoreModel implements Sortable
{
    use SortableMediaCategoryTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active', 'position', 'parent_id', 'name'
    ];

	/*public function files()
    {
        //return $this->hasMany('RefinedDigital\CMS\Modules\Medias\Model\Media')->orderby('position','asc');
    }*/

}
