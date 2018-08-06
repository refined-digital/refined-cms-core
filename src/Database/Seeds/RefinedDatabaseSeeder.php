<?php

namespace RefinedDigital\CMS\Database\Seeds;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RefinedDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TemplatesTableSeeder::class);
        $this->call(PageContentTypesSeeder::class);
        $this->call(PageHoldersSeeder::class);
        $this->call(PagesSeeder::class);
        $this->call(MediaSeeder::class);
    }
}
