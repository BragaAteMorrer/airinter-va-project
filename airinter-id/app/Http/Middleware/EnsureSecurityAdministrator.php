<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecurityAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $subjects = (array) config('argos-security.admin_subjects', []);

        abort_unless(
            $user && in_array((string) $user->subject, $subjects, true),
            403
        );

        return $next($request);
    }
}
