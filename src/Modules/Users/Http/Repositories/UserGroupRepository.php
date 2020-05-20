<?php

namespace RefinedDigital\CMS\Modules\Users\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;

class UserGroupRepository extends CoreRepository
{

    public function __construct()
    {
        $this->setModel('RefinedDigital\CMS\Modules\Users\Models\UserGroup');
    }
}
