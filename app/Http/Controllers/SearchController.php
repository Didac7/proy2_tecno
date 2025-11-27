<?php

namespace App\Http\Controllers;

use App\Models\Paquete;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    /**
     * Búsqueda global en el sistema
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (empty($query)) {
            return redirect()->back();
        }
        
        $resultados = [
            'paquetes' => [],
            'usuarios' => [],
            'vehiculos' => [],
            'rutas' => [],
        ];
        
        // Buscar paquetes
        $resultados['paquetes'] = Paquete::with(['cliente', 'ruta.origen', 'ruta.destino'])
            ->where(function($q) use ($query) {
                $q->where('codigo_seguimiento', 'LIKE', "%{$query}%")
                  ->orWhere('nombre_destinatario', 'LIKE', "%{$query}%")
                  ->orWhere('descripcion_contenido', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();
        
        // Buscar usuarios (solo si tiene permisos)
        $user = auth()->user();
        if ($user->isSecretaria() || $user->isPropietario()) {
            $resultados['usuarios'] = Usuario::where(function($q) use ($query) {
                $q->where('nombre', 'LIKE', "%{$query}%")
                  ->orWhere('apellido', 'LIKE', "%{$query}%")
                  ->orWhere('ci', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();
        }
        
        // Buscar vehículos
        $resultados['vehiculos'] = Vehiculo::with('chofer')
            ->where(function($q) use ($query) {
                $q->where('placa', 'LIKE', "%{$query}%")
                  ->orWhere('marca', 'LIKE', "%{$query}%")
                  ->orWhere('modelo', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();
        
        // Buscar rutas
        $resultados['rutas'] = Ruta::with(['origen', 'destino'])
            ->where(function($q) use ($query) {
                $q->where('nombre_ruta', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();
        
        return Inertia::render('Search/Results', [
            'query' => $query,
            'resultados' => $resultados,
        ]);
    }
}
