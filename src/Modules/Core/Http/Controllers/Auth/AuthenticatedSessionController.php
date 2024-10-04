<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RefinedDigital\CMS\Modules\Core\Traits\AuthTrait;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;
use RefinedDigital\CMS\Modules\Core\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    use AuthTrait;
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $repo = new PageRepository();
        $page = $repo->setAsPage('Login');
        return view('core::auth.login')
            ->with(compact('page'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (auth()->user()->user_level_id === 3 && config('auth.login_redirect')) {
            return redirect()->to(config('auth.login_redirect'));
        }

        return redirect()->intended(route($this->home, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
