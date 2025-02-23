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
        $offset = 1;
        if (help()->isMultiTenancy()) {
            $offset = 2;
        }
        if (request()->segment($offset) == 'refined') {

            $routeAggregate = app(ModuleAggregate::class);
            $menu = $routeAggregate->getMenuItems();
            // help()->trace($menu);
            // exit();
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
                $activeModule = request()->segment($offset + 1);
                $view->with('settings', true);
            }

            $view->with(compact('activeModule'));
            $view->with('activeOffset', $offset - 1);
        }

    }
}
