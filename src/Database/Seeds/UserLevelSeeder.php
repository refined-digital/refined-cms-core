<?php

namespace RefinedDigital\CMS\Database\Seeds;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class UserLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name'              => 'Super Admin',
            ],
            [
                'name'              => 'Admin',
            ],
            [
                'name'              => 'Member',
            ],
        ];

        foreach($data as $pos => $arg) {
            $args = [
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
                'active'        => 1,
                'position'      => $pos,
            ];
            $pageArgs = array_merge($arg, $args);
            DB::table('user_levels')->insert($pageArgs);
        }
    }
}
