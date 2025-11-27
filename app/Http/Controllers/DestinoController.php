<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DestinoController extends Controller
{
    public function index(Request $request)
    {
        $query = Destino::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('ciudad', 'ilike', "%{$search}%")
                  ->orWhere('direccion', 'ilike', "%{$search}%");
        }

        $destinos = $query->orderBy('ciudad')->paginate(10);

        return Inertia::render('Destinos/Index', [
            'destinos' => $destinos,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Destinos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ciudad' => 'required|string|max:100',
            'direccion' => 'required|string|max:200',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
        ]);

        Destino::create($validated);

        return redirect('/destinos')->with('success', 'Destino creado correctamente');
    }

    public function edit(Destino $destino)
    {
        return Inertia::render('Destinos/Edit', [
            'destino' => $destino
        ]);
    }

    public function update(Request $request, Destino $destino)
    {
        $validated = $request->validate([
            'ciudad' => 'required|string|max:100',
            'direccion' => 'required|string|max:200',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
        ]);

        $destino->update($validated);

        return redirect('/destinos')->with('success', 'Destino actualizado correctamente');
    }

    public function destroy(Destino $destino)
    {
        $destino->delete();
        return redirect('/destinos')->with('success', 'Destino eliminado correctamente');
    }
}
