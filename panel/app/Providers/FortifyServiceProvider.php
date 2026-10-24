<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pantallas del login, del código de 2 pasos y de confirmar contraseña
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        // Solo entran cuentas activas con la contraseña correcta
        Fortify::authenticateUsing(function (Request $request) {
            $usuario = User::where('email', $request->input(Fortify::username()))->first();

            $vencida = $usuario && $usuario->debe_cambiar_clave && $usuario->clave_temporal_expira?->isPast();

            if ($usuario && $usuario->activo && ! $vencida && Hash::check($request->input('password'), $usuario->password)) {
                return $usuario;
            }

            return null;
        });

        // Límite de intentos de login: 5 por correo+IP y 15 por correo (sin importar la IP)
        RateLimiter::for('login', function (Request $request) {
            $correo = Str::transliterate(Str::lower((string) $request->input(Fortify::username())));

            return [
                Limit::perMinute(5)->by($correo.'|'.$request->ip()),
                Limit::perMinute(15)->by('correo|'.$correo),
            ];
        });

        // Límite de intentos del código de 2 pasos
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
