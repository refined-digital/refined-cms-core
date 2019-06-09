<?php

namespace RefinedDigital\CMS\Modules\Users\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;

class UserRepository extends CoreRepository
{
	public function getAll()
    {
        return $this->model::
            with('userLevel')
            ->keywords()
            ->order()
            ->paging()
        ;
    }

}
