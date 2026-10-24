<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('accion', 60);                          // p. ej. 'caso.tomado', 'caso.visto'
            $table->foreignId('caso_id')->nullable()->constrained('casos')->nullOnDelete();
            $table->string('detalle')->nullable();                 // NUNCA datos del cliente
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
