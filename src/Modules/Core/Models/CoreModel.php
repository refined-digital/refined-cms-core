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
        $module = class_basename($this);
        $config = config(strtolower($module));
        $fields = $this->fieldsMergeConfig($fields, $config);

        // check if there are any extra fields
        $fields = $this->fieldsAddExtra($fields, $config);

        return json_decode(json_encode($fields));
    }

    public function fieldsMergeConfig($fields, $config)
    {
        if (isset($config['fields'])) {
            $configFields = $config['fields'];
            if (is_array($configFields) && sizeof($configFields)) {
                foreach ($configFields as $name => $data) {
                    $key = $this->findFieldKey($fields, $name);
                    $field =  array_get($fields, $key);
                    if ($field) {
                        $newField = array_merge($field, $data);
                        array_set($fields, $key, $newField);
                    }

                }
            }
        }

        return $fields;
    }

    public function findFieldKey($fields, $name)
    {
        $fieldKeys = $this->array_find_deep($fields, $name);
        array_pop($fieldKeys);
        $key = implode('.', $fieldKeys);

        return $key;
    }

    public function fieldsAddExtra($fields, $config)
    {
        // todo: make this more dynamic and nicer
        // todo: have an insert before
        if (isset($config['extra_fields'])) {
            $configFields = $config['extra_fields'];
            if (is_array($configFields) && sizeof($configFields)) {
                foreach ($configFields as $name => $data) {
                    $data['name'] = 'data__'.$data['name'];
                    if (isset($data['insertAfter'])) {
                        $key = $this->findFieldKey($fields, $data['insertAfter']);
                        if ($key) {
                            $keyBits = explode('.', $key);
                            if (sizeof($keyBits)) {
                                array_pop($keyBits);
                                $key = implode('.', $keyBits);

                                $siblings = array_get($fields, $key);
                                if (is_array($siblings)) {
                                    $newData = [];
                                    foreach ($siblings as $sib) {
                                        $newData[] = $sib;
                                        if ($sib['name'] == $data['insertAfter']) {
                                            $newData[] = $data;
                                        }
                                    }

                                    if (sizeof($newData)) {
                                        array_set($fields, $key, $newData);
                                    }
                                }
                            }
                        }
                    } else {
                        // todo: add the normal way
                    }
                }
            }
        }

        return $fields;
    }

    public function array_find_deep($array, $search, $keys = array())
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sub = $this->array_find_deep($value, $search, array_merge($keys, array($key)));
                if (count($sub)) {
                    return $sub;
                }
            } else if ($value === $search) {
                return array_merge($keys, array($key));
            }
        }

        return array();
    }
}
