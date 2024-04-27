<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RefinedDigital\CMS\Modules\Core\Traits\AuthTrait;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;

class EmailVerificationPromptController extends Controller
{
    use AuthTrait;
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route($this->home, absolute: false));
        }

        $repo = new PageRepository();
        $page = $repo->setAsPage('Verify Email');

        return view('core::auth.verify-email')->with(compact('page'));
    }
}
