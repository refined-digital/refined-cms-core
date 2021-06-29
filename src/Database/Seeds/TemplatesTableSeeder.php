<?php

namespace RefinedDigital\CMS\Database\Seeds;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class TemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            [
                'name'      => 'General',
                'source'    => 'general',
                'active'    => 1,
            ],
            [
                'name'      => 'Home',
                'source'    => 'home',
                'active'    => 1,
            ],
            [
                'name'      => 'Forms',
                'source'    => 'forms',
                'active'    => 1,
                'has_forms' => true
            ],
        ];

        foreach($templates as $pos => $u) {
            $args = [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'position' => $pos,
            ];
            $data = array_merge($args, $u);
            DB::table('templates')->insert($data);
        }
    }
}
