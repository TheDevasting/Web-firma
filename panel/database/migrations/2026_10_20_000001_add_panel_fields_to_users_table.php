<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol', 20)->default('abogado');            // 'admin' o 'abogado'
            $table->boolean('activo')->default(true);                  // false = cuenta desactivada
            $table->boolean('debe_cambiar_clave')->default(false);     // true = clave temporal
            $table->timestamp('clave_temporal_expira')->nullable();    // vencimiento de la clave temporal
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'activo', 'debe_cambiar_clave', 'clave_temporal_expira']);
        });
    }
};
