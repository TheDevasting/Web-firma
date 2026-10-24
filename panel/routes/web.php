<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CasoController;
use App\Http\Controllers\ClaveController;
use App\Http\Middleware\CambiarClaveTemporal;
use App\Http\Middleware\Exigir2fa;
use App\Http\Middleware\SoloAdmin;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    // Cambio de contraseña (también es la pantalla obligatoria con clave temporal)
    Route::get('/clave', [ClaveController::class, 'editar'])->name('clave.cambiar');
    Route::put('/clave', [ClaveController::class, 'actualizar'])->name('clave.actualizar');

    Route::middleware(CambiarClaveTemporal::class)->group(function () {
        // Aquí se activa la verificación en dos pasos (requiere confirmar la contraseña)
        Route::view('/seguridad', 'seguridad')
            ->middleware('password.confirm')
            ->name('seguridad');

        // Todo lo demás exige tener la verificación en dos pasos confirmada
        Route::middleware(Exigir2fa::class)->group(function () {
            Route::get('/dashboard', function () {
                return auth()->user()->esAdmin()
                    ? redirect()->route('admin.index')
                    : redirect()->route('casos.index');
            })->name('dashboard');

            Route::get('/casos', [CasoController::class, 'index'])->name('casos.index');
            Route::get('/casos/{caso}', [CasoController::class, 'show'])->name('casos.show');
            Route::post('/casos/{caso}/tomar', [CasoController::class, 'tomar'])->name('casos.tomar');

            // Zona del administrador (pide confirmar la contraseña)
            Route::middleware([SoloAdmin::class, 'password.confirm'])
                ->prefix('admin')->name('admin.')->group(function () {
                    Route::get('/', [AdminController::class, 'index'])->name('index');
                    Route::post('/abogados', [AdminController::class, 'crear'])->name('crear');
                    Route::post('/abogados/{usuario}/resetear', [AdminController::class, 'resetear'])->name('resetear');
                    Route::post('/abogados/{usuario}/activo', [AdminController::class, 'alternarActivo'])->name('activo');
                    Route::post('/abogados/{usuario}/quitar-2fa', [AdminController::class, 'quitar2fa'])->name('quitar2fa');
                });
        });
    });
});
