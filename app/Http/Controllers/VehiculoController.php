<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Vehiculo::with('chofer');
        
        // Si es chofer, solo ve su vehículo asignado
        if ($user->tipo_usuario === 'CHOFER') {
            $query->where('id_chofer', $user->id_usuario);
        }
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        $vehiculos = $query->orderBy('id_vehiculo', 'desc')->paginate(15);
        
        return Inertia::render('Vehiculos/Index', [
            'vehiculos' => $vehiculos,
            'filters' => $request->only(['estado']),
        ]);
    }

    public function create()
    {
        $choferes = Usuario::where('tipo_usuario', 'CHOFER')
            ->where('estado', 'ACTIVO')
            ->get();
        
        return Inertia::render('Vehiculos/Create', [
            'choferes' => $choferes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'placa' => 'required|unique:vehiculos',
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'año' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'capacidad_carga' => 'required|numeric|min:0',
            'tipo_vehiculo' => 'required|in:CAMION,CAMIONETA,FURGON,MOTO',
            'id_chofer' => 'nullable|exists:usuarios,id_usuario',
            'gps_activo' => 'boolean',
        ], [
            'placa.unique' => 'Esta placa ya está registrada',
            'año.max' => 'El año no puede ser mayor al actual',
        ]);
        
        $validated['estado'] = 'DISPONIBLE';
        $validated['gps_activo'] = $request->boolean('gps_activo', true);
        
        Vehiculo::create($validated);
        
        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo registrado exitosamente');
    }

    public function show(Vehiculo $vehiculo)
    {
        $vehiculo->load(['chofer', 'paquetes']);
        
        return Inertia::render('Vehiculos/Show', [
            'vehiculo' => $vehiculo,
        ]);
    }

    public function edit(Vehiculo $vehiculo)
    {
        $choferes = Usuario::where('tipo_usuario', 'CHOFER')
            ->where('estado', 'ACTIVO')
            ->get();
        
        return Inertia::render('Vehiculos/Edit', [
            'vehiculo' => $vehiculo,
            'choferes' => $choferes,
        ]);
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $validated = $request->validate([
            'placa' => 'required|unique:vehiculos,placa,' . $vehiculo->id_vehiculo . ',id_vehiculo',
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'capacidad_carga' => 'required|numeric|min:0',
            'estado' => 'required|in:DISPONIBLE,EN_RUTA,MANTENIMIENTO,FUERA_DE_SERVICIO',
            'id_chofer' => 'nullable|exists:usuarios,id_usuario',
        ]);
        
        $vehiculo->update($validated);
        
        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado exitosamente');
    }

    public function actualizarGPS(Request $request, Vehiculo $vehiculo)
    {
        $validated = $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ], [
            'latitud.between' => 'La latitud debe estar entre -90 y 90',
            'longitud.between' => 'La longitud debe estar entre -180 y 180',
        ]);
        
        $vehiculo->update([
            'latitud_actual' => $validated['latitud'],
            'longitud_actual' => $validated['longitud'],
            'ultima_actualizacion_gps' => now(),
        ]);
        
        return back()->with('success', 'Ubicación GPS actualizada');
    }
}
