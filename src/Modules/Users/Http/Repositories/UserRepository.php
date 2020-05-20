<?php

namespace RefinedDigital\CMS\Modules\Users\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;

class UserRepository extends CoreRepository
{
    public function __construct()
    {
        $this->setModel('RefinedDigital\CMS\Modules\Users\Models\User');
    }

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

    public function syncUserGroups($id, $userGroups)
    {
        // first delete all records
        \DB::table('user_user_group')
            ->whereUserId($id)
            ->delete();

        // now add the new groups
        $groupIds = explode(',', $userGroups);
        if (sizeof($groupIds)) {
            foreach ($groupIds as $groupId) {
                \DB::table('user_user_group')
                    ->insert([
                        'user_id' => $id,
                        'user_group_id' => $groupId
                    ]);
            }
        }
    }

}
