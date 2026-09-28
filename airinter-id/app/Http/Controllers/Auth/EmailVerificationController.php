<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountSecurityService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('account')
            : view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request, AccountSecurityService $security): RedirectResponse
    {
        if (!$request->user()->hasVerifiedEmail()) {
            $request->fulfill();
            $security->record($request, $request->user(), 'email.verified');
        }

        return redirect()->route('account')->with('status', 'Adresse e-mail vérifiée.');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Lien de vérification envoyé.');
    }
}
