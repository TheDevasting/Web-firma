<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['slug', 'nombre'];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function casos(): HasMany
    {
        return $this->hasMany(Caso::class);
    }
}
