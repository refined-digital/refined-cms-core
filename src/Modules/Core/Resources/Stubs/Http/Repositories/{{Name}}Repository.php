<?php

namespace App\RefinedCMS\{{FullName}}\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;

class {{Name}}Repository extends CoreRepository
{
    public function __construct()
    {
        $this->setModel('App\RefinedCMS\{{FullName}}\Models\{{Name}}');
    }

}
