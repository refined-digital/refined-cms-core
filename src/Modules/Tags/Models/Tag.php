<?php

namespace RefinedDigital\CMS\Modules\Tags\Models;

use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Core\Forms\Tab;
use RefinedDigital\CMS\Modules\Core\Forms\Section;
use RefinedDigital\CMS\Modules\Core\Forms\Block;
use RefinedDigital\CMS\Modules\Core\Forms\Row;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\TextInput;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Field;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\RichEditor;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Image;
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


    public function formSchema(): array
    {
        return [
            Tab::make('Content')->schema([
                Section::left()->schema([
                    Block::make('Content')->schema([
                        Row::make([
                            TextInput::make('name', 'Name')->required(),
                            Field::make('type', 'Type')->type('tagType')->required(),
                        ]),
                        RichEditor::make('content', 'Content'),
                    ]),
                ]),
                Section::right()->schema([
                    Block::make('Image')->schema([
                        Image::make('image', 'Image')->hideLabel(),
                    ]),
                ]),
            ]),
        ];
    }

}
