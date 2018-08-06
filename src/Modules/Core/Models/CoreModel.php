<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;

class CoreModel extends Model
{
    use SortableTrait;

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



    public function getFormFields()
    {
        $fields = $this->formFields;
        if (isset($this->isPage) && $this->isPage) {
            $fields[] = [
                'name' => 'Meta Data',
                'fields' => [
                    [
                        [ 'label' => 'URL', 'name' => 'meta[uri]', 'type' => 'url'],
                    ],
                    [
                        [ 'label' => 'Page Title', 'name' => 'meta[title]', 'type' => 'Title', 'note' => 'This area appears in the title of the browser<br/>A maximum of 70 characters is allowed<br/><img src="'.asset('vendor/refinedcms/img/ui/meta-title.png').'"/>'],
                        [ 'label' => 'Meta Description', 'name' => 'meta[description]', 'type' => 'textarea', 'note' => 'This area is used to describe the business to search engines<br>A maximum of <code>160</code> characters is allowed' ],
                    ]
                ]
            ];
        }

        // check if we have a module config
        // todo: update this to be more dynamic
        $module = class_basename($this);
        $config = config(strtolower($module).'.fields');
        if (is_array($config) && sizeof($config)) {
            foreach ($fields as $key => $fieldSet) {
                if (isset($fieldSet['blocks']) && is_array($fieldSet['blocks'])) {
                    foreach ($fieldSet['blocks'] as $bKey => $block) {
                        if (is_array($block)) {
                            foreach ($block as $fgKey => $fieldGroup) {
                                if (isset($fieldGroup['fields']) && is_array($fieldGroup['fields'])) {
                                    foreach ($fieldGroup['fields'] as $iKey => $inputFields) {
                                        foreach ($inputFields as $fKey => $field) {
                                            if (isset($config[$field['name']])) {
                                                $fields[$key]['blocks'][$bKey][$fgKey]['fields'][$iKey][$fKey] = array_merge($field, $config[$field['name']]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return json_decode(json_encode($fields));
    }
}
