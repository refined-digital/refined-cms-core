<?php

namespace RefinedDigital\CMS\Modules\Tags\Models;

use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Tags\Traits\IsTag;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Tag extends CoreModel implements Sortable
{
    use SortableTrait, IsTag;

    protected $templateId = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'position', 'name', 'type'
    ];

}
