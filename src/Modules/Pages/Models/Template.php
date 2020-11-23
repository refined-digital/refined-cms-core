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
        'active', 'position', 'has_forms', 'name', 'source',
    ];

    protected $casts = [
      'id' => 'integer',
      'active' => 'integer',
      'position' => 'integer',
      'has_forms' => 'integer',
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
                    [ 'label' => 'Has Forms', 'name' => 'has_forms', 'type' => 'select', 'options' => [0 => 'No', 1 => 'Yes'] ],
                ]
            ]
        ]
    ];

}
