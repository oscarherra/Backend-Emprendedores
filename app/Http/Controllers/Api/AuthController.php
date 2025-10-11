<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validar los datos de entrada (email y password)
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Intentar autenticar al usuario
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // 3. Si las credenciales son correctas, obtener el usuario
        $user = User::where('email', $request->email)->firstOrFail();

        // 4. Revocar tokens antiguos y crear uno nuevo
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Devolver el token y los datos del usuario
        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
}