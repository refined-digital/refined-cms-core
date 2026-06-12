<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddColourSetToPageContentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('page_content_types')) {
            return;
        }

        // fresh installs are populated by the seeder, only patch existing installs
        if (!DB::table('page_content_types')->count()) {
            return;
        }

        if (DB::table('page_content_types')->where('name', 'Colour Set')->exists()) {
            return;
        }

        // id must match RefinedDigital\CMS\Modules\Core\Enums\PageContentType::COLOUR_SET
        DB::table('page_content_types')->insert([
            'id'         => 13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'active'     => 1,
            'position'   => 12,
            'name'       => 'Colour Set',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('page_content_types')) {
            return;
        }

        DB::table('page_content_types')->where('name', 'Colour Set')->delete();
    }
}
