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
    protected $casts = [];

    protected $excerptLength = 200;
    protected $excerptType = 'character';

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

    public function getModelImagesAttribute()
    {
        if (isset($this->attributes['images']) && $this->attributes['images']) {
            return json_decode(json_decode($this->attributes['images']));
        }

        return [];
    }

    public function getExcerptAttribute()
    {
        $data = $this->content;
        if (isset($this->text) && isset($this->content)) {
            $data = $this->text;
        }

        $content = strip_tags($data);
        $search = ['&nbsp;', '’'];
        $replace = [' ', "'"];
        $content = str_replace($search, $replace, $content);

        $length = $this->excerptLength;

        if ($this->excerptType === 'word') {
            $wordsInText = preg_split('/([\s\n\r]+)/u', $content, null, PREG_SPLIT_DELIM_CAPTURE);
            $wordsWithNoSpaces = array_filter($wordsInText, function($word) {
                if ($word === ' ') {
                    return false;
                }

                return $word;
            });
            $newContent = array_slice($wordsWithNoSpaces, 0, $length);
            $excerpt = implode(' ', $newContent);
        } else {
            $excerpt = substr($content, 0, $length);
        }

        if (strlen($content) > strlen($excerpt)) {
            $excerpt .= '<span>...</span>';
        }

        return $excerpt;
    }

    public function addToAppends($data)
    {
        if (property_exists($this, 'appends')) {
            $this->appends = array_merge($this->appends, $data);
        }
    }

    public function addToCasts($data)
    {
        if (property_exists($this, 'casts')) {
            $this->casts = array_merge($this->casts, $data);
        }
    }

}
