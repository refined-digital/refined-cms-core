<?php

namespace RefinedDigital\CMS\Database\Seeds;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class PageHoldersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = [
            'Sitemap',
            'Footer Pages',
            'Hidden Pages',
        ];

        foreach ($levels as $pos => $level) {
            DB::table('page_holders')->insert([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'active'    => 1,
                'position'  => $pos,
                'name'      => $level
            ]);
        }
    }
}
