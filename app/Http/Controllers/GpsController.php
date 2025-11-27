<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GpsController extends Controller
{
    /**
     * Iniciar rastreo GPS para el vehículo del chofer
     */
    public function iniciarRastreo(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Verificar que sea un chofer
            if ($user->tipo_usuario !== 'CHOFER') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los choferes pueden activar el GPS'
                ], 403);
            }

            // Buscar el vehículo asignado al chofer
            $vehiculo = Vehiculo::where('id_chofer', $user->id_usuario)->first();

            if (!$vehiculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un vehículo asignado'
                ], 404);
            }

            // Activar GPS
            $vehiculo->update([
                'gps_activo' => true,
                'ultima_actualizacion_gps' => now(),
            ]);

            Log::info('GPS activado', [
                'vehiculo_id' => $vehiculo->id_vehiculo,
                'chofer_id' => $user->id_usuario
            ]);

            return response()->json([
                'success' => true,
                'message' => 'GPS activado correctamente',
                'vehiculo' => $vehiculo
            ]);

        } catch (\Exception $e) {
            Log::error('Error al iniciar rastreo GPS', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al activar el GPS'
            ], 500);
        }
    }

    /**
     * Actualizar ubicación GPS del vehículo
     */
    public function actualizarUbicacion(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
            ]);

            $user = auth()->user();
            
            // Buscar el vehículo del chofer
            $vehiculo = Vehiculo::where('id_chofer', $user->id_usuario)->first();

            if (!$vehiculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un vehículo asignado'
                ], 404);
            }

            // Actualizar ubicación
            $vehiculo->update([
                'latitud_actual' => $validated['latitud'],
                'longitud_actual' => $validated['longitud'],
                'ultima_actualizacion_gps' => now(),
                'gps_activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación actualizada'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar ubicación GPS', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar ubicación'
            ], 500);
        }
    }

    /**
     * Finalizar rastreo GPS
     */
    public function finalizarRastreo(Request $request)
    {
        try {
            $user = auth()->user();
            
            $vehiculo = Vehiculo::where('id_chofer', $user->id_usuario)->first();

            if (!$vehiculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un vehículo asignado'
                ], 404);
            }

            // Desactivar GPS y limpiar ubicación
            $vehiculo->update([
                'gps_activo' => false,
                'latitud_actual' => null,
                'longitud_actual' => null,
            ]);

            Log::info('GPS desactivado', [
                'vehiculo_id' => $vehiculo->id_vehiculo,
                'chofer_id' => $user->id_usuario
            ]);

            return response()->json([
                'success' => true,
                'message' => 'GPS desactivado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al finalizar rastreo GPS', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar el GPS'
            ], 500);
        }
    }

    /**
     * Obtener ubicación actual de un vehículo (para el mapa de rastreo)
     */
    public function obtenerUbicacion($vehiculoId)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($vehiculoId);

            return response()->json([
                'success' => true,
                'gps_activo' => $vehiculo->gps_activo,
                'latitud' => $vehiculo->latitud_actual,
                'longitud' => $vehiculo->longitud_actual,
                'ultima_actualizacion' => $vehiculo->ultima_actualizacion_gps,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vehículo no encontrado'
            ], 404);
        }
    }

    /**
     * Obtener estado del GPS para el dashboard del chofer
     */
    public function obtenerEstado()
    {
        try {
            $user = auth()->user();
            
            $vehiculo = Vehiculo::where('id_chofer', $user->id_usuario)->first();

            if (!$vehiculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un vehículo asignado',
                    'tiene_vehiculo' => false
                ]);
            }

            return response()->json([
                'success' => true,
                'tiene_vehiculo' => true,
                'gps_activo' => $vehiculo->gps_activo,
                'ultima_actualizacion' => $vehiculo->ultima_actualizacion_gps,
                'vehiculo' => [
                    'id' => $vehiculo->id_vehiculo,
                    'placa' => $vehiculo->placa,
                    'modelo' => $vehiculo->modelo,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado del GPS'
            ], 500);
        }
    }
}
