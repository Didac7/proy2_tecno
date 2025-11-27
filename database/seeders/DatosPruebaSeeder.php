<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Destino;
use App\Models\Ruta;
use App\Models\Vehiculo;
use App\Models\Usuario;
use App\Models\Paquete;
use App\Models\Pago;

class DatosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Destinos
        $destinos = [
            ['nombre_destino' => 'Santa Cruz', 'direccion' => 'Av. Banzer 4to Anillo', 'ciudad' => 'Santa Cruz', 'departamento' => 'Santa Cruz'],
            ['nombre_destino' => 'La Paz', 'direccion' => 'Terminal de Buses', 'ciudad' => 'La Paz', 'departamento' => 'La Paz'],
            ['nombre_destino' => 'Cochabamba', 'direccion' => 'Av. Ayacucho', 'ciudad' => 'Cochabamba', 'departamento' => 'Cochabamba'],
            ['nombre_destino' => 'Tarija', 'direccion' => 'Av. Las Américas', 'ciudad' => 'Tarija', 'departamento' => 'Tarija'],
        ];

        foreach ($destinos as $d) {
            Destino::create($d);
        }

        // 2. Crear Rutas
        $scz = Destino::where('nombre_destino', 'Santa Cruz')->first();
        $lpz = Destino::where('nombre_destino', 'La Paz')->first();
        $cba = Destino::where('nombre_destino', 'Cochabamba')->first();

        Ruta::create([
            'nombre_ruta' => 'SCZ - LPZ',
            'id_origen' => $scz->id_destino,
            'id_destino' => $lpz->id_destino,
            'distancia_km' => 850,
            'tiempo_estimado_horas' => 12,
            'costo_base' => 150,
            'estado' => 'ACTIVA'
        ]);

        Ruta::create([
            'nombre_ruta' => 'SCZ - CBA',
            'id_origen' => $scz->id_destino,
            'id_destino' => $cba->id_destino,
            'distancia_km' => 450,
            'tiempo_estimado_horas' => 7,
            'costo_base' => 80,
            'estado' => 'ACTIVA'
        ]);

        // 3. Crear Vehículos
        $chofer = Usuario::where('tipo_usuario', 'CHOFER')->first();
        
        Vehiculo::create([
            'placa' => '2025-ABC',
            'marca' => 'Volvo',
            'modelo' => 'FH16',
            'anio' => 2022,
            'capacidad_carga' => 20000,
            'tipo_vehiculo' => 'CAMION',
            'estado' => 'DISPONIBLE',
            'id_chofer' => $chofer ? $chofer->id_usuario : null,
            'gps_activo' => true
        ]);

        Vehiculo::create([
            'placa' => '4040-XYZ',
            'marca' => 'Toyota',
            'modelo' => 'Hilux',
            'anio' => 2023,
            'capacidad_carga' => 1000,
            'tipo_vehiculo' => 'CAMIONETA',
            'estado' => 'DISPONIBLE',
            'id_chofer' => null,
            'gps_activo' => true
        ]);

        // 4. Crear Paquetes de Prueba
        $cliente = Usuario::where('tipo_usuario', 'CLIENTE')->first();
        $ruta = Ruta::first();
        $vehiculo = Vehiculo::first();

        if ($cliente && $ruta) {
            $paquete = Paquete::create([
                'codigo_seguimiento' => 'ASD-2025-002',
                'id_remitente' => $cliente->id_usuario,
                'id_destinatario' => $cliente->id_usuario,
                'id_ruta' => $ruta->id_ruta,
                'id_vehiculo' => $vehiculo->id_vehiculo,
                'descripcion_contenido' => 'Documentos',
                'precio' => 1.00,
                'tipo_paquete' => 'GENERAL',
                'prioridad' => 'NORMAL',
                'estado_paquete' => 'REGISTRADO',
                'direccion_entrega' => 'Oficina-Central-Trans_Velasco',
                'nombre_destinatario' => 'Julian Valverde Masqui',
                'telefono_destinatario' => '70012345'
            ]);

            // Crear Pago pendiente para este paquete
            Pago::create([
                'id_paquete' => $paquete->id_paquete,
                'monto_total' => 1.00,
                'monto_pagado' => 0,
                'tipo_pago' => 'CONTADO',
                'estado_pago' => 'PENDIENTE'
            ]);
        }
    }
}
