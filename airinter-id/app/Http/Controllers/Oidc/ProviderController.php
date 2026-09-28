<?php

namespace App\Http\Controllers\Oidc;

use App\Http\Controllers\Controller;
use App\Services\OidcTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function discovery(OidcTokenService $tokens): JsonResponse
    {
        $issuer = $tokens->issuer();

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'userinfo_endpoint' => $issuer.'/oauth/userinfo',
            'jwks_uri' => $issuer.'/.well-known/jwks.json',
            'scopes_supported' => ['openid', 'profile', 'email', 'promethee:read', 'hermes:operate'],
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post', 'none'],
            'code_challenge_methods_supported' => ['S256'],
            'claims_supported' => ['sub', 'name', 'email', 'email_verified', 'locale', 'zoneinfo'],
        ])->header('Cache-Control', 'public, max-age=300');
    }

    public function oauthMetadata(OidcTokenService $tokens): JsonResponse
    {
        $issuer = $tokens->issuer();

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'jwks_uri' => $issuer.'/.well-known/jwks.json',
            'scopes_supported' => ['openid', 'profile', 'email', 'promethee:read', 'hermes:operate'],
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post', 'none'],
            'code_challenge_methods_supported' => ['S256'],
        ])->header('Cache-Control', 'public, max-age=300');
    }

    public function jwks(OidcTokenService $tokens): JsonResponse
    {
        return response()->json($tokens->jwks())
            ->header('Cache-Control', 'public, max-age=300');
    }

    public function userinfo(Request $request): JsonResponse
    {
        $user = $request->user();

        $claims = ['sub' => $user->subject];

        if ($user->tokenCan('profile')) {
            $claims += [
                'name' => $user->display_name,
                'locale' => $user->preferred_locale,
                'zoneinfo' => $user->timezone,
            ];
        }

        if ($user->tokenCan('email')) {
            $claims += [
                'email' => $user->email,
                'email_verified' => $user->email_verified_at !== null,
            ];
        }

        return response()->json($claims);
    }
}
