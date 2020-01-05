<?php

namespace RefinedDigital\CMS\Modules\Users\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;

class UserRepository extends CoreRepository
{
	public function getAll()
    {
        $loggedInUserLevel = auth()->user()->user_level_id;

        return $this->model::
            with('userLevel')
            ->where('user_level_id', '>=', $loggedInUserLevel)
            ->keywords()
            ->order()
            ->paging()
        ;
    }

}
