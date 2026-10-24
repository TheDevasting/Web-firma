<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'usuarios' => User::with('areas')->orderBy('name')->get(),
            'areas'    => Area::orderBy('nombre')->get(),
            'bitacora' => Bitacora::with('usuario')->latest()->limit(40)->get(),
        ]);
    }

    public function crear(Request $request)
    {
        $datos = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:255', 'unique:users,email'],
            'areas'   => ['required', 'array', 'min:1'],
            'areas.*' => ['exists:areas,id'],
        ]);

        $clave = $this->claveTemporal();

        $usuario = new User(['name' => $datos['name'], 'email' => $datos['email'], 'password' => $clave]);
        // Por la interfaz solo se crean abogados; los administradores se crean a mano en el servidor
        $usuario->forceFill([
            'rol'                   => 'abogado',
            'activo'                => true,
            'debe_cambiar_clave'    => true,
            'clave_temporal_expira' => now()->addHours(48),
        ])->save();
        $usuario->areas()->sync($datos['areas']);

        Bitacora::registrar('usuario.creado', null, $usuario->email);

        return back()->with('clave_temporal', ['correo' => $usuario->email, 'clave' => $clave]);
    }

    public function resetear(Request $request, User $usuario)
    {
        $this->noEsUnoMismo($request, $usuario);

        $clave = $this->claveTemporal();
        $usuario->forceFill([
            'password'              => $clave,
            'debe_cambiar_clave'    => true,
            'clave_temporal_expira' => now()->addHours(48),
        ])->save();
        $this->cerrarSesiones($usuario);

        Bitacora::registrar('usuario.clave_restablecida', null, $usuario->email);

        return back()->with('clave_temporal', ['correo' => $usuario->email, 'clave' => $clave]);
    }

    public function alternarActivo(Request $request, User $usuario)
    {
        $this->noEsUnoMismo($request, $usuario);

        $usuario->forceFill(['activo' => ! $usuario->activo])->save();
        if (! $usuario->activo) {
            $this->cerrarSesiones($usuario);
        }

        Bitacora::registrar($usuario->activo ? 'usuario.activado' : 'usuario.desactivado', null, $usuario->email);

        return back()->with('status', 'Cuenta '.($usuario->activo ? 'activada' : 'desactivada').'.');
    }

    public function quitar2fa(Request $request, User $usuario)
    {
        $this->noEsUnoMismo($request, $usuario);

        $usuario->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ])->save();
        $this->cerrarSesiones($usuario);

        Bitacora::registrar('usuario.2fa_desactivado', null, $usuario->email);

        return back()->with('status', 'Verificación en dos pasos desactivada. Deberá configurarla de nuevo.');
    }

    private function claveTemporal(): string
    {
        return Str::password(16, symbols: false);
    }

    private function noEsUnoMismo(Request $request, User $usuario): void
    {
        abort_if($request->user()->id === $usuario->id, 403);
    }

    // Cierra las sesiones abiertas de esa cuenta (funciona si las sesiones están en la base de datos)
    private function cerrarSesiones(User $usuario): void
    {
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $usuario->id)->delete();
        }
    }
}
