<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('paquetes', function (Blueprint $table) {
            $table->dropColumn(['peso_kg', 'volumen_m3', 'valor_declarado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paquetes', function (Blueprint $table) {
            $table->decimal('peso_kg', 10, 2)->nullable();
            $table->decimal('volumen_m3', 10, 2)->nullable();
            $table->decimal('valor_declarado', 10, 2)->nullable();
        });
    }
};
