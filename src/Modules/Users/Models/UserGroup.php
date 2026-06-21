<?php

namespace RefinedDigital\CMS\Modules\Users\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Core\Forms\Tab;
use RefinedDigital\CMS\Modules\Core\Forms\Row;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\TextInput;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Select;
use Spatie\EloquentSortable\Sortable;

class UserGroup extends CoreModel implements Sortable
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active', 'position', 'name',
    ];

    /**
     * The fields to be displayed for creating / editing
     */
    public function formSchema(): array
    {
        return [
            Tab::make('Content')->schema([
                Row::make([
                    Select::make('active', 'Active')->required()->options([1 => 'Yes', 0 => 'No']),
                    TextInput::make('name', 'Name')->required(),
                ]),
            ]),
        ];
    }
}
