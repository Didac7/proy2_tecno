<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use App\Models\Destino;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RutaController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruta::with(['origen', 'destino']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('origen', function($q) use ($search) {
                $q->where('ciudad', 'ilike', "%{$search}%");
            })->orWhereHas('destino', function($q) use ($search) {
                $q->where('ciudad', 'ilike', "%{$search}%");
            });
        }

        $rutas = $query->orderBy('id_ruta')->paginate(10);

        return Inertia::render('Rutas/Index', [
            'rutas' => $rutas,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Rutas/Create', [
            'destinos' => Destino::orderBy('ciudad')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origen_id' => 'required|exists:destinos,id_destino',
            'destino_id' => 'required|exists:destinos,id_destino|different:origen_id',
            'distancia_km' => 'required|numeric|min:0',
            'tiempo_estimado' => 'required|string',
        ]);

        Ruta::create($validated);

        return redirect('/rutas')->with('success', 'Ruta creada correctamente');
    }

    public function edit(Ruta $ruta)
    {
        return Inertia::render('Rutas/Edit', [
            'ruta' => $ruta,
            'destinos' => Destino::orderBy('ciudad')->get()
        ]);
    }

    public function update(Request $request, Ruta $ruta)
    {
        $validated = $request->validate([
            'origen_id' => 'required|exists:destinos,id_destino',
            'destino_id' => 'required|exists:destinos,id_destino|different:origen_id',
            'distancia_km' => 'required|numeric|min:0',
            'tiempo_estimado' => 'required|string',
        ]);

        $ruta->update($validated);

        return redirect('/rutas')->with('success', 'Ruta actualizada correctamente');
    }

    public function destroy(Ruta $ruta)
    {
        $ruta->delete();
        return redirect('/rutas')->with('success', 'Ruta eliminada correctamente');
    }
}
