<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CaptchaController extends Controller
{
    public function verify(Request $request)
    {
        $secretKey = env('RECAPTCHA_SECRET_KEY'); // lo pones en .env
        $token = $request->input('token');

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token faltante.'], 400);
        }

        // Llamar a Google para verificar el token
        $googleResponse = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $request->ip()
            ]
        );

        $result = $googleResponse->json();

        if (isset($result['success']) && $result['success'] == true) {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Captcha inválido'
        ], 400);
    }
}
