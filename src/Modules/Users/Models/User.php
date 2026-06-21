<?php

namespace RefinedDigital\CMS\Modules\Users\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use RefinedDigital\CMS\Modules\Core\Traits\EditFormFieldsTrait;
use RefinedDigital\CMS\Modules\Core\Traits\ExtraFillableFieldsTrait;
use RefinedDigital\CMS\Modules\Core\Forms\Tab;
use RefinedDigital\CMS\Modules\Core\Forms\Block;
use RefinedDigital\CMS\Modules\Core\Forms\Row;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\TextInput;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Field;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Select;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Password;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use RefinedDigital\CMS\Modules\Users\Traits\UserLevel;

class User extends Authenticatable implements Sortable
{
    use Notifiable, SortableTrait, SoftDeletes, UserLevel, EditFormFieldsTrait, ExtraFillableFieldsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active', 'position', 'user_level_id', 'first_name', 'last_name', 'email', 'password',
    ];

    protected $casts = [
      'id' => 'integer',
      'active' => 'integer',
      'position' => 'integer',
      'user_level_id' => 'integer',
      'user_group_id' => 'integer',
    ];

    protected $config = 'users';

    public $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = [
        'name',
    ];

    protected $with = [
        'groups',
    ];


    /**
     * The fields to be displayed for creating / editing
     */
    public function formSchema(): array
    {
        return [
            Tab::make('User Details')->schema([
                Block::make('Profile')->schema([
                    Row::make([
                        Select::make('active', 'Active')->required()->options([1 => 'Yes', 0 => 'No']),
                        Field::make('user_level_id', 'User Level')->type('userLevels')->required()
                            ->note('User <strong>Admin</strong> for all administrators to edit website information<br/>Use <strong>Member</strong> for all users who login to the website'),
                        Field::make('groups', 'User Group')->type('userGroups'),
                    ]),
                    Row::make([
                        TextInput::make('first_name', 'First Name')->required(),
                        TextInput::make('last_name', 'Last Name')->required(),
                        TextInput::make('email', 'Email')->email()->required()->note('Used for login'),
                    ]),
                ]),
                Block::make('Password')->schema([
                    Row::make([
                        Password::make('password', 'Password')->required(),
                        Password::make('password_confirmation', 'Confirm Password')->required(),
                    ]),
                ]),
            ]),
        ];
    }

    public function groups()
    {
        return $this->belongsToMany(UserGroup::class);
    }

    public function getNameAttribute()
    {
        $n = [];
        if ($this->first_name) {
            $n[] = $this->first_name;
        }
        if ($this->last_name) {
            $n[] = $this->last_name;
        }

        return implode(' ', $n);
    }


	public function scopeActive($query)
	{
		$query->whereActive(1);
	}

	public function scopeOrder($query, $default = 'position', $direction = 'asc')
	{
        if(request()->has('sort')) {
            $sort = request()->get('sort');
        }

        if(request()->has('dir')) {
            $dir = request()->get('dir');
        }

		if(isset($sort) && isset($dir)) {
			$query->orderBy($sort, $dir);
		}

		$query->orderBy($default, $direction);
	}

	public function scopePaging($query, $perPage=20)
	{
		if(request()->has('perPage')) {
			$perPage = request()->get('perPage');

			if ($perPage == 'all') {
			    return $query->get();
			}
		}

		return $query->paginate($perPage);
	}

	public function scopeKeywords($query)
    {
        if(request()->has('keywords') && strlen(request()->get('keywords')) > 0) {
            $query
                ->where('first_name','LIKE','%'.request()->get('keywords').'%')
                ->orWhere('last_name','LIKE','%'.request()->get('keywords').'%')
                ->orWhere('email','LIKE','%'.request()->get('keywords').'%')
            ;
        }
    }
}
