<?php

namespace App\Http\Controllers;

use App\Models\Paquete;
use App\Models\Ruta;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaqueteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Paquete::with(['destinatario', 'remitente', 'ruta.origen', 'ruta.destino', 'vehiculo', 'pago']);
        
        // Filtrar por cliente si es un cliente
        if ($user->isCliente()) {
            $query->where(function($q) use ($user) {
                $q->where('id_remitente', $user->id_usuario)
                  ->orWhere('id_destinatario', $user->id_usuario);
            });
        }
        
        // Filtrar por vehículo si es un chofer
        if ($user->tipo_usuario === 'CHOFER') {
            $vehiculo = Vehiculo::where('id_chofer', $user->id_usuario)->first();
            if ($vehiculo) {
                $query->where('id_vehiculo', $vehiculo->id_vehiculo);
            } else {
                // Si el chofer no tiene vehículo asignado, no ve ningún paquete
                $query->whereRaw('1 = 0');
            }
        }
        
        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado_paquete', $request->estado);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('codigo_seguimiento', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_destinatario', 'LIKE', "%{$search}%");
            });
        }
        
        $paquetes = $query->orderBy('fecha_registro', 'desc')->paginate(15);
        
        return Inertia::render('Paquetes/Index', [
            'paquetes' => $paquetes,
            'filters' => $request->only(['estado', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rutas = Ruta::with(['origen', 'destino'])
            ->where('estado', 'ACTIVA')
            ->get();
        
        $vehiculos = Vehiculo::where('estado', 'DISPONIBLE')->get();
        
        $clientes = \App\Models\Usuario::where('tipo_usuario', 'CLIENTE')
            ->where('estado', 'ACTIVO')
            ->select('id_usuario', 'nombre', 'apellido', 'ci', 'email', 'telefono')
            ->orderBy('nombre')
            ->get();
        
        return Inertia::render('Paquetes/Create', [
            'rutas' => $rutas,
            'vehiculos' => $vehiculos,
            'clientes' => $clientes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_remitente' => 'required|exists:usuarios,id_usuario',
            'codigo_seguimiento' => 'required|unique:paquetes',
            'id_ruta' => 'required|exists:rutas,id_ruta',
            'id_vehiculo' => 'required|exists:vehiculos,id_vehiculo',
            'descripcion_contenido' => 'required|string',
            'precio' => 'required|numeric|min:1',
            'tipo_paquete' => 'required|in:GENERAL,FRAGIL,PERECEDERO',
            'prioridad' => 'required|in:NORMAL,URGENTE,EXPRESS',
            'direccion_entrega' => 'required|string',
            'nombre_destinatario' => 'required|string',
            'telefono_destinatario' => 'required|string',
        ], [
            'id_remitente.required' => 'Debe seleccionar un remitente',
            'codigo_seguimiento.required' => 'El código de seguimiento es obligatorio',
            'codigo_seguimiento.unique' => 'Este código ya existe',
            'id_ruta.required' => 'Debe seleccionar una ruta',
            'descripcion_contenido.required' => 'La descripción es obligatoria',
            'precio.required' => 'El precio del envío es obligatorio',
            'precio.min' => 'El precio debe ser al menos 1 Bs',
        ]);
        
        // Asignar valores por defecto para campos opcionales
        // id_destinatario puede ser null si no es un usuario registrado
        // direccion_recogida puede ser null
        
        $validated['estado_paquete'] = 'REGISTRADO';
        
        $paquete = Paquete::create($validated);
        
        return redirect()->route('paquetes.index')
            ->with('success', 'Paquete registrado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Paquete $paquete)
    {
        $paquete->load(['cliente', 'ruta.origen', 'ruta.destino', 'vehiculo.chofer', 'seguimientos', 'pagos', 'pago.planPago']);
        
        return Inertia::render('Paquetes/Show', [
            'paquete' => $paquete,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paquete $paquete)
    {
        $rutas = Ruta::with(['origen', 'destino'])->where('estado', 'ACTIVA')->get();
        $vehiculos = Vehiculo::where('estado', 'DISPONIBLE')->get();
        
        return Inertia::render('Paquetes/Edit', [
            'paquete' => $paquete,
            'rutas' => $rutas,
            'vehiculos' => $vehiculos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paquete $paquete)
    {
        $validated = $request->validate([
            'descripcion_contenido' => 'required|string',
            'precio' => 'required|numeric|min:1',
            'estado_paquete' => 'required|in:REGISTRADO,PENDIENTE,EN_ALMACEN,EN_TRANSITO,ENTREGADO,CANCELADO',
            'direccion_entrega' => 'required|string',
            'nombre_destinatario' => 'required|string',
            'telefono_destinatario' => 'required|string',
            'id_vehiculo' => 'nullable|exists:vehiculos,id_vehiculo',
        ]);
        
        $paquete->update($validated);
        
        return redirect()->route('paquetes.index')
            ->with('success', 'Paquete actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paquete $paquete)
    {
        $paquete->update(['estado_paquete' => 'CANCELADO']);
        
        return redirect()->route('paquetes.index')
            ->with('success', 'Paquete cancelado exitosamente');
    }

    /**
     * Marcar paquete como entregado
     */
    public function entregar(Paquete $paquete)
    {
        $paquete->update([
            'estado_paquete' => 'ENTREGADO',
            'fecha_entrega_real' => now(),
        ]);
        
        return redirect()->route('paquetes.show', $paquete)
            ->with('success', 'Paquete marcado como entregado');
    }
}
