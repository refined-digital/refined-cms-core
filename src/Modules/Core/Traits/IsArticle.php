<?php

namespace RefinedDigital\CMS\Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait IsArticle
{
    /**
     * Boot the is page trait for a model.
     *
     * @return void
     */
    public static function bootIsArticle()
    {
    }

    protected function getArrayableAppends()
    {
        $appends = ['paging'];
        foreach ($appends as $field) {
            if (!in_array($field, $this->appends)){
                $this->appends[] = $field;
            }
        }

        return parent::getArrayableAppends();
    }

    public function getPagingAttribute()
    {
        $order = 'position';
        $orderDirection = 'asc';
        // todo: check this, we might need to update how it works
        if ($this->order) {
            $order = $this->order['column'];
            $orderDirection = $this->order['direction'];
            if ($orderDirection == 'desc') {
                $orderDirection = 'asc';
            }
        }
        $paging = new \stdClass();
        $link = new \stdClass();
        $link->id = '';
        $link->name = '';
        $link->link = '';

        $paging->total = 0;
        $paging->current = 0;
        $paging->next = clone $link;
        $paging->previous = clone $link;


        $data = static::whereActive(1)->orderBy($order, $orderDirection);

        if (method_exists($this, 'articleFilter')) {
            $data = $this->articleFilter($data);
        }

        $data = $data->get();
        
        if ($data && $data->count()) {
            // get the base
            $uri = request()->path();
            $uriBits = explode('/', $uri);
            if (sizeof($uriBits) > 1) {
                array_pop($uriBits);
            }
            $base = '/'.implode('/', $uriBits);

            // set the numbers
            $paging->total = $data->count();
            $current = $data->search(function($value, $key) {
                if ($this->id == $value->id) {
                    return $key;
                }
            });

            $next = $data->get($current + 1);
            $previous = $data->get($current - 1);

            if (isset($next->id, $next->meta->uri)) {
                $paging->next->id = $next->id;
                $paging->next->name = $next->name;
                $paging->next->link = $base.'/'.$next->meta->uri;
            }
            if (isset($previous->id, $previous->meta->uri)) {
                $paging->previous->id = $previous->id;
                $paging->previous->name = $previous->name;
                $paging->previous->link = $base.'/'.$previous->meta->uri;
            }

            // current is an index, so update it to a number
            $paging->current = $current + 1;

        }

        return $paging;
    }

}
