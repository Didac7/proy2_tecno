<?php

namespace App\Http\Controllers;

use App\Models\Paquete;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RastreoController extends Controller
{
    /**
     * Mostrar página de rastreo con mapa (GPS REAL)
     */
    public function show($codigo)
    {
        // Buscar el paquete por código de seguimiento
        $paquete = Paquete::with(['ruta.origen', 'ruta.destino', 'vehiculo'])
            ->where('codigo_seguimiento', $codigo)
            ->firstOrFail();

        // Obtener coordenadas de origen y destino
        $origen = $this->getCoordenadas($paquete->ruta->origen->nombre_destino);
        $destino = $this->getCoordenadas($paquete->ruta->destino->nombre_destino);

        // Obtener ubicación actual del vehículo (si tiene GPS activo)
        $vehiculoUbicacion = null;
        if ($paquete->vehiculo) {
            if ($paquete->vehiculo->gps_activo && 
                $paquete->vehiculo->latitud_actual && 
                $paquete->vehiculo->longitud_actual) {
                $vehiculoUbicacion = [
                    'lat' => floatval($paquete->vehiculo->latitud_actual),
                    'lng' => floatval($paquete->vehiculo->longitud_actual),
                ];
            }
        }

        return Inertia::render('Rastreo/Show', [
            'paquete' => $paquete,
            'origen' => $origen,
            'destino' => $destino,
            'vehiculoUbicacion' => $vehiculoUbicacion,
        ]);
    }

    /**
     * Obtener coordenadas de ciudades bolivianas
     */
    private function getCoordenadas($ciudad)
    {
        $coordenadas = [
            'La Paz' => ['lat' => -16.5000, 'lng' => -68.1500],
            'Santa Cruz' => ['lat' => -17.8146, 'lng' => -63.1561],
            'Cochabamba' => ['lat' => -17.3935, 'lng' => -66.1570],
            'Sucre' => ['lat' => -19.0332, 'lng' => -65.2627],
            'Oruro' => ['lat' => -17.9833, 'lng' => -67.1167],
            'Potosí' => ['lat' => -19.5836, 'lng' => -65.7531],
            'Tarija' => ['lat' => -21.5355, 'lng' => -64.7296],
            'Trinidad' => ['lat' => -14.8333, 'lng' => -64.9000],
            'Cobija' => ['lat' => -11.0267, 'lng' => -68.7692],
        ];

        return $coordenadas[$ciudad] ?? $coordenadas['La Paz'];
    }
}
