<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Caso extends Model
{
    protected $table = 'casos';

    protected $fillable = [
        'referencia', 'area_id', 'nombre', 'correo', 'telefono', 'mensaje',
        'estado', 'tomado_por', 'tomado_en',
    ];

    // Los datos del cliente se cifran y descifran solos con la APP_KEY
    protected function casts(): array
    {
        return [
            'nombre'    => 'encrypted',
            'correo'    => 'encrypted',
            'telefono'  => 'encrypted',
            'mensaje'   => 'encrypted',
            'tomado_en' => 'datetime',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function tomadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tomado_por');
    }

    /**
     * Intenta tomar el caso. Es UNA sola actualización condicional en la base de datos:
     * si dos abogados pulsan a la vez, solo a uno le afecta 1 fila; al otro, 0.
     */
    public static function tomar(int $id, User $usuario): bool
    {
        $filas = static::where('id', $id)
            ->where('estado', 'nueva')
            ->whereNull('tomado_por')
            ->update([
                'estado'     => 'tomada',
                'tomado_por' => $usuario->id,
                'tomado_en'  => now(),
            ]);

        return $filas === 1;
    }
}
