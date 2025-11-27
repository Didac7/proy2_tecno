<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::query();
        
        if ($request->filled('tipo_usuario')) {
            $query->where('tipo_usuario', $request->tipo_usuario);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('apellido', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('ci', 'LIKE', "%{$search}%");
            });
        }
        
        $usuarios = $query->orderBy('id_usuario', 'desc')->paginate(15);
        
        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'filters' => $request->only(['tipo_usuario', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Usuarios/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ci' => 'required|unique:usuarios',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'tipo_usuario' => 'required|in:PROPIETARIO,CHOFER,SECRETARIA,CLIENTE',
            'telefono' => 'required|string',
            'email' => 'required|email|unique:usuarios',
            'password' => 'required|min:6',
            'direccion' => 'nullable|string',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ], [
            'ci.required' => 'El CI es obligatorio',
            'ci.unique' => 'Este CI ya está registrado',
            'email.unique' => 'Este email ya está registrado',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        // $validated['estado'] ya viene en el request y está validado
        
        Usuario::create($validated);
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    public function show(Usuario $usuario)
    {
        $usuario->load(['paquetes', 'vehiculo']);
        
        return Inertia::render('Usuarios/Show', [
            'usuario' => $usuario,
        ]);
    }

    public function edit(Usuario $usuario)
    {
        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario,
        ]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        // Verificar permisos: Secretaria no puede editar Propietarios
        if (auth()->user()->isSecretaria() && $usuario->isPropietario()) {
            return redirect()->back()->withErrors(['error' => 'No tienes permiso para editar a un Propietario.']);
        }

        $validated = $request->validate([
            'ci' => 'required|unique:usuarios,ci,' . $usuario->id_usuario . ',id_usuario',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'telefono' => 'required|string',
            'email' => 'required|email|unique:usuarios,email,' . $usuario->id_usuario . ',id_usuario',
            'direccion' => 'nullable|string',
            'tipo_usuario' => 'required|in:PROPIETARIO,CHOFER,SECRETARIA,CLIENTE',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);
        
        // Secretaria no puede cambiar roles a Propietario
        if (auth()->user()->isSecretaria() && $validated['tipo_usuario'] === 'PROPIETARIO') {
            return redirect()->back()->withErrors(['tipo_usuario' => 'No puedes asignar el rol de Propietario.']);
        }

        $usuario->update($validated);
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy(Usuario $usuario)
    {
        $usuario->update(['estado' => 'INACTIVO']);
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario inactivado exitosamente');
    }
}
