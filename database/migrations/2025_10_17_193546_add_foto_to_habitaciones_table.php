<?php

// database/migrations/xxxx_xx_xx_add_foto_to_habitaciones_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('habitaciones', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('precio_noche');
        });
    }

    public function down(): void {
        Schema::table('habitaciones', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
