<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    protected $table = 'bitacora';

    protected $fillable = ['user_id', 'accion', 'caso_id', 'detalle', 'ip'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Registra una acción. En 'detalle' nunca pongas datos del cliente.
     */
    public static function registrar(string $accion, ?int $casoId = null, ?string $detalle = null): void
    {
        static::create([
            'user_id' => auth()->id(),
            'accion'  => $accion,
            'caso_id' => $casoId,
            'detalle' => $detalle,
            'ip'      => request()->ip(),
        ]);
    }
}
