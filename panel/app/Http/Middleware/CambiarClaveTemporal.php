<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CambiarClaveTemporal
{
    /**
     * Quien entró con una clave temporal debe cambiarla antes de ver cualquier otra cosa.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->debe_cambiar_clave) {
            return redirect()->route('clave.cambiar');
        }

        return $next($request);
    }
}
