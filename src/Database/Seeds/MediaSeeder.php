<?php

namespace RefinedDigital\CMS\Database\Seeds;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class MediaSeeder extends Seeder
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
                'parent_id'         => 0,
                'position'          => 0,
                'name'              => 'Media',
            ],
            [
                'parent_id'         => 1,
                'position'          => 0,
                'name'              => 'Images',
            ],
            [
                'parent_id'         => 1,
                'position'          => 1,
                'name'              => 'Files',
            ],
        ];

        foreach($data as $pos => $arg) {
            $args = [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'active'    => 1,
            ];
            $pageArgs = array_merge($arg, $args);
            DB::table('media_categories')->insert($pageArgs);
        }
    }
}
