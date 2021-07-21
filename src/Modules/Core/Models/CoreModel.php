<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use RefinedDigital\CMS\Modules\Core\Traits\ClearResponseCacheTrait;
use RefinedDigital\CMS\Modules\Core\Traits\EditFormFieldsTrait;
use Spatie\EloquentSortable\SortableTrait;

class CoreModel extends Model
{
    use SortableTrait, EditFormFieldsTrait, ClearResponseCacheTrait;

    protected $appends = [];

    protected $order = [ 'column' => 'position', 'direction' => 'asc'];

    public $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    public function scopeActive($query)
	{
		$query->whereActive(1);
	}

	public function scopeOrder($query, $default = false, $direction = false)
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

		if (!$default) {
		    $default = $this->order['column'];
		}

		if (!$direction) {
		    $direction = $this->order['direction'];
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
                ->where('name','LIKE','%'.request()->get('keywords').'%')
            ;
        }
    }

    public function scopeSearch($query, $fields = [])
    {
        if(request()->has('keywords') && strlen(request()->get('keywords')) > 0 && sizeof($fields)) {
            $query->where(function($q) use ($fields) {
                foreach ($fields as $field) {
                    $q->orWhere($field, 'LIKE', '%'.request()->get('keywords').'%');
                }
            });
        }
    }

}
