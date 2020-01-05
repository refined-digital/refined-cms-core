<?php

namespace RefinedDigital\CMS\Modules\Users\Http\Repositories;

use RefinedDigital\CMS\Modules\Users\Models\UserLevel;

class Users {

    public function getUserLevelsForSelect()
    {
        $loggedInUserLevel = auth()->user()->user_level_id;

        $levels = UserLevel::whereActive(1)->where('id', '>=', $loggedInUserLevel)->get();
        $data = [0 => 'Please Select'];
        
        if ($levels && $levels->count()) {
            foreach ($levels as $level) {
                if ($level->id >= $loggedInUserLevel) {
                    $data[$level->id] = $level->name;
                }
            }
        }

        return $data;
    }

    public function getLoggedInUser()
    {
        $user = auth()->user();
        $userData = new \stdClass();
        $userData->id = $user->id;
        $userData->user_level_id = $user->user_level_id;
        $userData->name = $user->first_name.' '.$user->last_name;
        $userData->first_name = $user->first_name;
        $userData->last_name = $user->last_name;
        $userData->email = $user->email;

        return $userData;
    }

}
