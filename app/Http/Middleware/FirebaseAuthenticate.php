<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\FirebaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards both Blade routes (session-based) and API routes (Bearer token).
 *
 * Blade flow : the login controller stores the local user id in the session
 *              after verifying the Firebase token once. We trust the session
 *              here and just load the user.
 * API flow   : verify the Bearer ID token on every request.
 */
class FirebaseAuthenticate
{
    public function __construct(
        private readonly FirebaseAuthService $firebase
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Session-based (Blade views).
        if ($request->session()->has('user_id')) {
            $user = User::find($request->session()->get('user_id'));
            if ($user) {
                $request->setUserResolver(fn () => $user);
                return $next($request);
            }
            $request->session()->forget('user_id');
        }

        // 2. Bearer token (API).
        $token = $request->bearerToken();
        if ($token) {
            $claims = $this->firebase->verify($token);
            if ($claims) {
                $user = User::where('firebase_uid', $claims['sub'])->first();
                if ($user) {
                    $request->setUserResolver(fn () => $user);
                    return $next($request);
                }
            }
        }

        // 3. Reject.
        if ($request->expectsJson()) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }
        return redirect()->route('login');
    }
}
