<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();   // igual al valor del <select> del formulario
            $table->string('nombre');
            $table->timestamps();
        });

        // Qué abogados atienden cada área
        Schema::create('area_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['area_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_user');
        Schema::dropIfExists('areas');
    }
};
