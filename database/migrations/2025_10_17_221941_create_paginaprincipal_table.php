<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up()
    {
        Schema::create('paginaprincipal', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('foto')->nullable(); // Ruta de la imagen
            $table->timestamps();
        });

        // Opcional: Insertar un registro inicial
        DB::table('paginaprincipal')->insert([
            'titulo' => 'Bienvenido al Hotel ICI',
            'descripcion' => 'Lujo, confort y atención personalizada en el corazón de la ciudad.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('paginaprincipal');
    }
};