<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;

class UserLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = auth()->user();
        $passes = true;

        $route = explode('.', $request->route()->getName());
        $activeModule = $route[1];

        $routeAggregate = app(ModuleAggregate::class);
        $menu = $routeAggregate->getMenuItems();

        // loop and see if we have a max
        if (sizeof($menu)) {
            foreach ($menu as $key => $item) {
                if (isset($item->children) && sizeof($item->children)) {
                    foreach ($item->children as $childKey => $child) {
                        if ($child->route == $activeModule && isset($child->max_user_level_id) && $user->user_level_id > $child->max_user_level_id) {
                            $passes = false;
                        }
                    }
                }


                if ($item->route == $activeModule && isset($item->max_user_level_id) && $item->max_user_level_id && $user->user_level_id > $item->max_user_level_id) {
                    $passes = false;
                }

            }
        }

        //help()->trace(view()->getHints(),1);

        if (!$passes) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
