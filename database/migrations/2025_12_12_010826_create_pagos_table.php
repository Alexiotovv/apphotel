<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->enum('metodo_pago', ['tarjeta', 'qr', 'efectivo', 'transferencia', 'qr_pendiente']);
            $table->decimal('monto', 10, 2);
            $table->enum('estado', ['pendiente', 'completado', 'rechazado', 'reembolsado'])->default('pendiente');
            $table->string('referencia')->unique();
            $table->json('detalles')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos');
    }
};