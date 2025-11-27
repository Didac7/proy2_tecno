<?php

namespace App\Http\Controllers;

use App\Models\Paquete;
use App\Models\Seguimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeguimientoController extends Controller
{
    /**
     * Página de rastreo público
     */
    public function rastreo()
    {
        return Inertia::render('Seguimiento/Rastreo');
    }

    /**
     * Mostrar seguimiento de un paquete
     */
    public function mostrar($codigo)
    {
        $paquete = Paquete::with([
            'cliente',
            'ruta.origen',
            'ruta.destino',
            'vehiculo.chofer',
            'seguimientos' => function($query) {
                $query->orderBy('fecha_hora', 'desc');
            }
        ])->where('codigo_seguimiento', $codigo)->firstOrFail();
        
        return Inertia::render('Seguimiento/Mostrar', [
            'paquete' => $paquete,
        ]);
    }

    /**
     * Registrar nuevo seguimiento
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_paquete' => 'required|exists:paquetes,id_paquete',
            'id_vehiculo' => 'required|exists:vehiculos,id_vehiculo',
            'estado_seguimiento' => 'required|string',
            'ubicacion_actual' => 'required|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'descripcion' => 'nullable|string',
        ]);
        
        $validated['registrado_por'] = auth()->id();
        
        Seguimiento::create($validated);
        
        // Actualizar estado del paquete
        $paquete = Paquete::find($validated['id_paquete']);
        $paquete->update(['estado_paquete' => $validated['estado_seguimiento']]);
        
        return back()->with('success', 'Seguimiento registrado exitosamente');
    }
}
