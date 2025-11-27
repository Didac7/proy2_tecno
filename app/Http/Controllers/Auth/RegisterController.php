<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle registration request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ci' => 'required|unique:usuarios',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'telefono' => 'required|string',
            'email' => 'required|email|unique:usuarios',
            'password' => 'required|min:6|confirmed',
            'direccion' => 'nullable|string',
        ], [
            'ci.required' => 'El CI es obligatorio',
            'ci.unique' => 'Este CI ya está registrado',
            'email.unique' => 'Este email ya está registrado',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        // Assign default role CLIENTE
        $validated['tipo_usuario'] = 'CLIENTE';
        $validated['estado'] = 'ACTIVO';

        Usuario::create($validated);

        return redirect()->route('login')
            ->with('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
    }
}
