<?php

namespace RefinedDigital\CMS\Modules\Core\Http\ViewComposers;

use Illuminate\View\View;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;

class MenuComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (request()->segment(1) == 'refined') {

            $routeAggregate = app(ModuleAggregate::class);
            $menu = $routeAggregate->getMenuItems();
            $user = auth()->user();

            if (sizeof($menu)) {
                foreach ($menu as $key => $item) {
                    if (isset($item->children) && sizeof($item->children)) {
                        foreach ($item->children as $childKey => $child) {
                            if (isset($child->max_user_level_id) && $user->user_level_id > $child->max_user_level_id) {
                                unset($menu[$key]->children[$childKey]);
                            }
                        }
                    }


                    if (isset($item->max_user_level_id) && $item->max_user_level_id && $user->user_level_id > $item->max_user_level_id) {
                        unset($menu[$key]);
                    }

                }
            }

            $view->with(compact('menu'));

            $route = explode('.', \Route::currentRouteName());
            $activeModule = $route[1];

            if ($activeModule == 'settings') {
                $activeModule = request()->segment(2);
                $view->with('settings', true);
            }

            $view->with(compact('activeModule'));
        }

    }
}
