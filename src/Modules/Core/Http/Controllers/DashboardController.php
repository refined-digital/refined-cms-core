<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Controllers;

use Illuminate\Routing\Controller;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // same modules the sidebar shows, honouring the menu's level gate.
        // the MenuComposer only binds $menu to the layout's own scope, so the
        // tiles are built here for the view
        $tiles = [];
        foreach (app(ModuleAggregate::class)->getMenuItems() as $item) {
            if (isset($item->max_user_level_id) && $item->max_user_level_id && $user->user_level_id > $item->max_user_level_id) {
                continue;
            }
            $tiles[] = $item;
        }

        return view('core::pages.dashboard')->with([
            'module' => 'dashboard',
            'heading' => 'Dashboard',
            'tiles' => $tiles,
        ]);
    }
}
