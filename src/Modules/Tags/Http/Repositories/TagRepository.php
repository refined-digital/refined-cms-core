<?php

namespace RefinedDigital\CMS\Modules\Tags\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;

class TagRepository extends CoreRepository
{

    public function __construct()
    {
        $this->setModel('RefinedDigital\CMS\Modules\Tags\Models\Tag');
    }

	public function all()
    {
        return $this->model::
            orderBy('type')
            ->orderBy('position')
            ->get()
        ;
    }

	public function getAll()
    {
        $data = $this->model::
            keywords()
        ;

        if (!request()->has('sort')) {
            $data->orderBy('type');
        }

        $data = $data
            ->order('name')
            ->paging()
        ;

        return $data;
    }
}
