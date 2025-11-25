<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no está logueado o no es admin, devolvemos error
        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Acceso solo para administradores'], 403);
        }

        return $next($request);
    }
}
