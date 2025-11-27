<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'id_transaccion_pagofacil')) {
                $table->string('id_transaccion_pagofacil')->nullable()->after('numero_transaccion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'id_transaccion_pagofacil')) {
                $table->dropColumn('id_transaccion_pagofacil');
            }
        });
    }
};
