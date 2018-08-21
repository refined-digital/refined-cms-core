<?php

namespace RefinedDigital\CMS\Database\Seeds;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class PageContentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = [
            'Rich Text',
            'Static Text',
            'Plain Text',
            'Image',
            'File',
            'Select',
        ];

        foreach ($levels as $pos => $level) {
            DB::table('page_content_types')->insert([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'active'    => 1,
                'position'  => $pos,
                'name'      => $level
            ]);
        }
    }
}
