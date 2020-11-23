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
        'position', 'name', 'type', 'image', 'content'
    ];

    protected $casts = [
      'id' => 'integer',
      'position' => 'integer',
    ];


    public $formFields = [

        [
            'name' => 'Content',
            'sections' => [
                'left' => [
                    'blocks' => [
                        [
                            'name' => 'Content',
                            'fields' => [
                                [
                                    [ 'label' => 'Name', 'name' => 'name', 'required' => true],
                                    [ 'label' => 'Type', 'name' => 'type', 'type' => 'tagType', 'required' => true],
                                ],
                                [
                                    [ 'label' => 'Content', 'name' => 'content', 'type' => 'richtext'],
                                ]
                            ]
                        ]
                    ]
                ],
                'right' => [
                    'blocks' => [
                        [
                            'name' => 'Image',
                            'fields' => [
                                [
                                    [ 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'hideLabel' => true],
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        ],
   ];

}
