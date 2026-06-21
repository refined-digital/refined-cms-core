<?php

namespace RefinedDigital\CMS\Modules\Pages\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Core\Forms\Tab;
use RefinedDigital\CMS\Modules\Core\Forms\Row;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\TextInput;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Select;
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

    public function formSchema(): array
    {
        return [
            Tab::make('Content')->schema([
                Row::make([
                    Select::make('active', 'Active')->required()->options([1 => 'Yes', 0 => 'No']),
                ]),
                Row::make([
                    TextInput::make('name', 'Name')->required(),
                    TextInput::make('source', 'Source')->required()->note('Name of the blade files. Note, you do not need to add .blade.php'),
                    Select::make('has_forms', 'Has Forms')->options([0 => 'No', 1 => 'Yes']),
                ]),
            ]),
        ];
    }

}
