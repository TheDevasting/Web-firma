<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ClaveController extends Controller
{
    public function editar()
    {
        return view('clave.cambiar');
    }

    public function actualizar(Request $request)
    {
        $datos = $request->validate([
            'actual' => ['required', 'current_password'],
            'nueva'  => ['required', 'confirmed', 'different:actual', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        // forceFill: 'debe_cambiar_clave' no es asignable en bloque a propósito
        $request->user()->forceFill([
            'password'              => $datos['nueva'],
            'debe_cambiar_clave'    => false,
            'clave_temporal_expira' => null,
        ])->save();

        Bitacora::registrar('clave.cambiada');

        return redirect()->route('dashboard')->with('status', 'Contraseña actualizada.');
    }
}
