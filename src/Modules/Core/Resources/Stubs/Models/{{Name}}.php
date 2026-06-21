<?php

namespace App\RefinedCMS\{{FullName}}\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Core\Forms\Tab;
use RefinedDigital\CMS\Modules\Core\Forms\Section;
use RefinedDigital\CMS\Modules\Core\Forms\Block;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\TextInput;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Select;
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
     * The fields to be displayed for creating / editing.
     *
     * @return \RefinedDigital\CMS\Modules\Core\Forms\Tab[]
     */
    public function formSchema(): array
    {
        return [
            Tab::make('Content')->schema([
                Section::left()->schema([
                    Block::make('Content')->schema([
                        TextInput::make('name', 'Name')->required(){-page
                            ->attrs(['v-model' => 'content.name', '@keyup' => 'updateSlug'])-},
                    ]),
                ]),
                Section::right()->schema([
                    Block::make('Settings')->schema([
                        Select::make('active', 'Active')->required()->options([1 => 'Yes', 0 => 'No']),
                    ]),
                ]),
            ]),
        ];
    }
}
