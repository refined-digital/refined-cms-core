<?php

namespace RefinedDigital\CMS\Modules\Pages\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use Spatie\EloquentSortable\Sortable;

class Template extends CoreModel implements Sortable
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active', 'position', 'name', 'source',
    ];

    public $formFields = [
        [
            'name' => 'Content',
            'fields' => [
                [
                    [ 'label' => 'Active', 'name' => 'active', 'required' => true, 'type' => 'select', 'options' => [1 => 'Yes', 0 => 'No'] ],
                ],
                [
                    [ 'label' => 'Name', 'name' => 'name', 'required' => true ],
                    [ 'label' => 'Source', 'name' => 'source', 'required' => true, 'note' => 'Name of the blade files. Note, you do not need to add .blade.php' ],
                ]
            ]
        ]
    ];

}
