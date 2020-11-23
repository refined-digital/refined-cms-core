<?php

namespace RefinedDigital\CMS\Modules\Settings\Models;

use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use Spatie\EloquentSortable\SortableTrait;

class Setting extends CoreModel
{
    use SortableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'position', 'required', 'name', 'model', 'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'position' => 'integer',
        'required' => 'integer',
        'value' => 'object',
    ];

}
