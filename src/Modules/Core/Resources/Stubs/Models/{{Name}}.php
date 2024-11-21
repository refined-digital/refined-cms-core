<?php

namespace App\RefinedCMS\{{FullName}}\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Pages\Traits\IsPage;
use Spatie\EloquentSortable\Sortable;

class {{Name}} extends CoreModel implements Sortable
{
    use SoftDeletes{-page, IsPage-};

    protected $fillable = [
        'active',
        'position',
        'name'
    ];
    {-page
    protected $casts = [
        'data' => 'object'
    ];

    protected $templateId = {{templateId}};
    -}
    /**
     * The fields to be displayed for creating / editing
     *
     * @var array
     */
    public function formFields(): array
    {
        return [
            'name' => 'Content',
            'sections' => [
                'left' => [
                    'blocks' => [
                        [
                            'name' => 'Content',
                            'fields' => [
                                [
                                    [
                                        'label' => 'Name',
                                        'name' => 'name',
                                        'required' => true{-page ,
                                        'attrs' => ['v-model' => 'content.name', '@keyup' => 'updateSlug' ]-}
                                    ],
                                ]
                            ]
                        ]
                    ],
                ],
                'right' => [
                    'blocks' => [
                        [
                            'name' => 'Settings',
                            'fields' => [
                                [
                                    [
                                        'label' => 'Active',
                                        'name' => 'active',
                                        'required' => true,
                                        'type' => 'select',
                                        'options' => [1 => 'Yes', 0 => 'No']
                                    ],
                                ],
                            ]
                        ]
                    ]
                ],
            ],
        ];
    }
}
