<?php

/**
 * Handle the authentication for the API layer
 */

namespace App\Http\Middleware;

use App\Contracts\Middleware;
use App\Models\Enums\UserState;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiAuth implements Middleware
{
    /**
     * Handle an incoming request.
     *
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // A short-lived ACARS bearer token is preferred for the desktop client.
        // Keep the historical API-key flow for existing third-party clients.
        $api_key = $request->header('x-api-key', null);
        $authorization = $request->header('Authorization', '');
        $user = null;

        if (Str::startsWith($authorization, 'Bearer ')) {
            $token = trim(Str::after($authorization, 'Bearer '));
            if ($token !== '') {
                $tokenRecord = DB::table('acars_access_tokens')
                    ->where('token_hash', hash('sha256', $token))
                    ->whereNull('revoked_at')
                    ->where('expires_at', '>', now())
                    ->first();

                if ($tokenRecord !== null) {
                    $user = User::find($tokenRecord->user_id);
                    DB::table('acars_access_tokens')->where('id', $tokenRecord->id)->update([
                        'last_used_at' => now(),
                    ]);
                }
            }
        } elseif ($api_key === null && $authorization !== '') {
            // phpVMS historically accepted a raw Authorization header as an API key.
            $api_key = $authorization;
        }

        if ($user === null && $api_key !== null) {
            $user = User::where('api_key', $api_key)->first();
        }

        if ($user === null) {
            return $this->unauthorized('Invalid credentials');
        }

        if ($user->state !== UserState::ACTIVE && $user->state !== UserState::ON_LEAVE) {
            return $this->unauthorized('User is not ACTIVE, please contact an administrator');
        }

        // Set the user to the request
        Auth::setUser($user);
        $request->merge(['user' => $user]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Force english locale for API
        app()->setLocale('en');

        return $next($request);
    }

    /**
     * Return an unauthorized message
     *
     * @param  mixed                                                                                    $details
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    private function unauthorized($details = '')
    {
        return response([
            'error' => [
                'code'      => '401',
                'http_code' => 'Unauthorized',
                'message'   => 'Invalid or missing API key ('.$details.')',
            ],
        ], 401);
    }
}
