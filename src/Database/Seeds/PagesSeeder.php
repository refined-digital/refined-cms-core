<?php

namespace RefinedDigital\CMS\Database\Seeds;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class PagesSeeder extends Seeder
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
                'page_holder_id'    => 1,
                'parent_id'         => 0,
                'page_type'         => 1,
                'template_id'       => 2,
                'position'          => 0,
                'name'              => 'Home',
                'uri'               => '',
            ],
            [
                'page_holder_id'    => 1,
                'parent_id'         => 0,
                'page_type'         => 1,
                'template_id'       => 1,
                'position'          => 1,
                'name'              => 'About Us',
                'uri'               => 'about-us',
            ],
            [
                'page_holder_id'    => 1,
                'parent_id'         => 0,
                'page_type'         => 1,
                'template_id'       => 3,
                'position'          => 2,
                'form_id'           => 1,
                'name'              => 'Contact Us',
                'uri'               => 'contact-us',
            ],
            [
                'page_holder_id'    => 2,
                'parent_id'         => 0,
                'page_type'         => 1,
                'template_id'       => 1,
                'position'          => 1,
                'name'              => 'Privacy Policy',
                'uri'               => 'privacy-policy',
            ],
        ];

        foreach($data as $pos => $arg) {
            $args = [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'active'    => 1,
            ];
            $pageArgs = array_merge($arg, $args);
            unset($pageArgs['uri']);
            unset($pageArgs['template_id']);
            DB::table('pages')->insert($pageArgs);

            // add the uri
            $uri = [
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
                'template_id'   => $arg['template_id'],
                'uri'           => $arg['uri'],
                'name'          => $arg['name'],
                'title'         => $arg['name'],
                'uriable_id'    => $pos+1,
                'uriable_type'  => 'RefinedDigital\CMS\Modules\Pages\Models\Page',
            ];
            DB::table('uri')->insert($uri);
        }
    }
}
