<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RefinedDigital\CMS\Modules\Core\Traits\AuthTrait;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;

class ConfirmablePasswordController extends Controller
{
    use AuthTrait;
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        $repo = new PageRepository();
        $page = $repo->setAsPage('Confirm Password');
        return view('core::auth.confirm-password')
            ->with(compact('page'));
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route($this->home, absolute: false));
    }
}
