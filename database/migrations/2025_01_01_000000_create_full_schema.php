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
        // 1. Usuarios
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('ci', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->enum('tipo_usuario', ['PROPIETARIO', 'CHOFER', 'SECRETARIA', 'CLIENTE']);
            $table->string('telefono', 20);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('direccion')->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->rememberToken();
            
            // Índices
            $table->index('email');
            $table->index('tipo_usuario');
        });

        // 2. Destinos
        Schema::create('destinos', function (Blueprint $table) {
            $table->id('id_destino');
            $table->string('nombre_destino', 100);
            $table->string('direccion', 200);
            $table->string('ciudad', 50);
            $table->string('departamento', 50);
            $table->string('pais', 50)->default('Bolivia');
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            
            // Índices
            $table->index('ciudad');
        });

        // 3. Rutas
        Schema::create('rutas', function (Blueprint $table) {
            $table->id('id_ruta');
            $table->string('nombre_ruta', 100);
            $table->unsignedBigInteger('id_origen');
            $table->unsignedBigInteger('id_destino');
            $table->decimal('distancia_km', 10, 2);
            $table->decimal('tiempo_estimado_horas', 5, 2);
            $table->decimal('costo_base', 10, 2);
            $table->text('descripcion_ruta')->nullable();
            $table->enum('estado', ['ACTIVA', 'INACTIVA'])->default('ACTIVA');
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->foreign('id_origen')
                ->references('id_destino')
                ->on('destinos')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('id_destino')
                ->references('id_destino')
                ->on('destinos')
                ->onDelete('restrict')
                ->onUpdate('cascade');
                
            // Índices
            $table->index('estado');
        });

        // 4. Vehículos
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('id_vehiculo');
            $table->string('placa', 20)->unique();
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->integer('anio');
            $table->decimal('capacidad_carga', 10, 2);
            $table->enum('tipo_vehiculo', ['CAMION', 'CAMIONETA', 'FURGON', 'MOTO']);
            $table->enum('estado', ['DISPONIBLE', 'EN_RUTA', 'MANTENIMIENTO', 'FUERA_DE_SERVICIO'])->default('DISPONIBLE');
            $table->unsignedBigInteger('id_chofer')->nullable();
            $table->decimal('latitud_actual', 10, 8)->nullable();
            $table->decimal('longitud_actual', 11, 8)->nullable();
            $table->timestamp('ultima_actualizacion_gps')->nullable();
            $table->boolean('gps_activo')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();

            $table->foreign('id_chofer')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('set null')
                ->onUpdate('cascade');
                
            // Índices
            $table->index('estado');
            $table->index('id_chofer');
        });

        // 5. Paquetes (CORREGIDO)
        Schema::create('paquetes', function (Blueprint $table) {
            $table->id('id_paquete');
            $table->string('codigo_seguimiento', 50)->unique();
            $table->unsignedBigInteger('id_remitente'); // NUEVO: quien envía
            $table->unsignedBigInteger('id_destinatario'); // RENOMBRADO: quien recibe
            $table->unsignedBigInteger('id_ruta');
            $table->unsignedBigInteger('id_vehiculo')->nullable();
            $table->text('descripcion_contenido');
            $table->decimal('peso_kg', 10, 2);
            $table->decimal('volumen_m3', 10, 2);
            $table->decimal('valor_declarado', 10, 2);
            $table->enum('tipo_paquete', ['GENERAL', 'FRAGIL', 'PERECEDERO']);
            $table->enum('prioridad', ['NORMAL', 'URGENTE', 'EXPRESS'])->default('NORMAL');
            $table->enum('estado_paquete', ['REGISTRADO', 'PENDIENTE', 'EN_ALMACEN', 'EN_TRANSITO', 'ENTREGADO', 'CANCELADO'])->default('REGISTRADO');
            $table->string('direccion_recogida', 200);
            $table->string('direccion_entrega', 200);
            $table->string('nombre_destinatario', 100);
            $table->string('telefono_destinatario', 20);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_entrega_estimada')->nullable();
            $table->timestamp('fecha_entrega_real')->nullable();

            $table->foreign('id_remitente')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('id_destinatario')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('id_ruta')
                ->references('id_ruta')
                ->on('rutas')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')
                ->on('vehiculos')
                ->onDelete('set null')
                ->onUpdate('cascade');
                
            // Índices
            $table->index('codigo_seguimiento');
            $table->index('estado_paquete');
            $table->index(['id_remitente', 'estado_paquete']);
            $table->index(['id_destinatario', 'estado_paquete']);
        });

        // 6. Pagos (CORREGIDO - sin id_cliente redundante)
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->unsignedBigInteger('id_paquete')->unique(); // Un pago por paquete
            $table->decimal('monto_total', 10, 2);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->enum('tipo_pago', ['CONTADO', 'CREDITO']);
            $table->enum('estado_pago', ['PENDIENTE', 'PARCIAL', 'PAGADO', 'ANULADO'])->default('PENDIENTE');
            $table->enum('metodo_pago', ['EFECTIVO', 'TRANSFERENCIA', 'QR', 'TARJETA'])->nullable();
            $table->string('numero_transaccion', 100)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();

            $table->foreign('id_paquete')
                ->references('id_paquete')
                ->on('paquetes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
                
            // Índices
            $table->index('estado_pago');
            $table->index(['id_paquete', 'estado_pago']);
        });

        // 7. Planes de Pago (MEJORADO)
        Schema::create('planes_pago', function (Blueprint $table) {
            $table->id('id_plan');
            $table->unsignedBigInteger('id_pago')->unique();
            $table->integer('numero_cuotas');
            $table->decimal('monto_cuota', 10, 2);
            $table->integer('cuotas_pagadas')->default(0);
            $table->timestamps();

            $table->foreign('id_pago')
                ->references('id_pago')
                ->on('pagos')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // 8. Cuotas (NUEVA TABLA)
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id('id_cuota');
            $table->unsignedBigInteger('id_plan');
            $table->integer('numero_cuota');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->enum('estado', ['PENDIENTE', 'PAGADA', 'VENCIDA'])->default('PENDIENTE');
            $table->string('numero_transaccion', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_plan')
                ->references('id_plan')
                ->on('planes_pago')
                ->onDelete('cascade')
                ->onUpdate('cascade');
                
            // Índices
            $table->index('estado');
            $table->index(['id_plan', 'numero_cuota']);
        });

        // 9. Seguimiento (MEJORADO)
        Schema::create('seguimiento', function (Blueprint $table) {
            $table->id('id_seguimiento');
            $table->unsignedBigInteger('id_paquete');
            $table->unsignedBigInteger('id_vehiculo')->nullable();
            $table->unsignedBigInteger('registrado_por')->nullable(); // CORREGIDO: ahora es FK
            $table->string('estado_seguimiento', 50);
            $table->string('ubicacion_actual', 200);
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_hora')->useCurrent();

            $table->foreign('id_paquete')
                ->references('id_paquete')
                ->on('paquetes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')
                ->on('vehiculos')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->foreign('registrado_por')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('set null')
                ->onUpdate('cascade');
                
            // Índices
            $table->index('id_paquete');
            $table->index('fecha_hora');
        });

        // 10. Notificaciones (NUEVA TABLA)
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->unsignedBigInteger('id_usuario');
            $table->string('titulo');
            $table->text('mensaje');
            $table->enum('tipo', ['INFO', 'WARNING', 'ERROR', 'SUCCESS'])->default('INFO');
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('cascade')
                ->onUpdate('cascade');
                
            // Índices
            $table->index(['id_usuario', 'leida']);
        });

        // 11. Menús
        Schema::create('menus', function (Blueprint $table) {
            $table->id('id_menu');
            $table->string('nombre', 100);
            $table->string('icono', 50)->nullable();
            $table->string('url', 200)->nullable();
            $table->integer('orden')->default(0);
            $table->unsignedBigInteger('padre_id')->nullable();
            $table->json('roles')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('padre_id')
                ->references('id_menu')
                ->on('menus')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // 12. Visitas Página
        Schema::create('visitas_pagina', function (Blueprint $table) {
            $table->id();
            $table->string('pagina')->unique();
            $table->integer('contador')->default(0);
            $table->timestamps();
        });
        
        // Tablas de Laravel
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas_pagina');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('seguimiento');
        Schema::dropIfExists('cuotas');
        Schema::dropIfExists('planes_pago');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('paquetes');
        Schema::dropIfExists('vehiculos');
        Schema::dropIfExists('rutas');
        Schema::dropIfExists('destinos');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache');
    }
};
