<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;

class Admin
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

        if ($user->user_level_id > 2) {
            return redirect('/');
        }

        return $next($request);
    }
}
