<?php

namespace RefinedDigital\CMS\Modules\Core\Http\ViewComposers;

use Illuminate\View\View;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;

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
        $routeAggregate = app(ModuleAggregate::class);
        $menu = $routeAggregate->getMenuItems();
        ksort($menu);
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