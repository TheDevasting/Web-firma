<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Exigir2fa
{
    /**
     * Solo deja pasar a quien ya confirmó la verificación en dos pasos.
     * Los demás van a la pantalla de Seguridad para activarla.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->two_factor_confirmed_at) {
            return redirect()->route('seguridad')
                ->with('status', 'Activa la verificación en dos pasos para continuar.');
        }

        return $next($request);
    }
}
