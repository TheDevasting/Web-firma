<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 12)->unique();            // código corto para la lista (sin datos personales)
            $table->foreignId('area_id')->constrained();

            // Datos del cliente: se guardan CIFRADOS (por eso son text)
            $table->text('nombre');
            $table->text('correo');
            $table->text('telefono');
            $table->text('mensaje');

            $table->string('estado', 20)->default('nueva')->index(); // nueva, tomada, cerrada
            $table->foreignId('tomado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tomado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casos');
    }
};
