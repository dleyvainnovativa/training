<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly FirebaseAuthService $firebase
    ) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Called by the front-end after Firebase sign-in succeeds client-side.
     * Verifies the ID token server-side, then opens a Laravel session.
     * Manager-only: the first verified account is provisioned as manager;
     * further unknown accounts are rejected.
     */
    public function session(Request $request)
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $claims = $this->firebase->verify($data['id_token']);
        if (! $claims) {
            return response()->json(['message' => 'Token inválido o expirado.'], 401);
        }

        $user = User::where('firebase_uid', $claims['sub'])->first();

        if (! $user) {
            // Provision only if no manager exists yet (single-manager system).
            if (User::where('role', 'manager')->exists()) {
                return response()->json(['message' => 'Esta cuenta no tiene acceso.'], 403);
            }
            $user = User::create([
                'name'         => $claims['name'] ?? 'Administrador',
                'email'        => $claims['email'] ?? '',
                'firebase_uid' => $claims['sub'],
                'role'         => 'manager',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('user_id', $user->id);

        return response()->json(['redirect' => route('dashboard')]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
