<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Solo estos campos se pueden asignar en bloque.
     * 'rol', 'activo' y 'debe_cambiar_clave' NO están aquí a propósito:
     * se asignan de forma explícita en el código del administrador,
     * para que ningún formulario pueda darse permisos a sí mismo.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Campos que nunca se muestran si el usuario se convierte a JSON o array.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'activo'                => 'boolean',
            'debe_cambiar_clave'    => 'boolean',
            'clave_temporal_expira' => 'datetime',
        ];
    }

    /**
     * Áreas del derecho que atiende este abogado.
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }
}
