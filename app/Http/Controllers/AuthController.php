<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::where('email', $credentials['email'])->first();

        if ($usuario && Hash::check($credentials['password'], $usuario->password)) {
            Auth::login($usuario, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'ci' => 'required|unique:usuarios',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'telefono' => 'required|string',
            'email' => 'required|email|unique:usuarios',
            'direccion' => 'nullable|string',
            'password' => 'required|min:6|confirmed',
        ]);

        // Crear usuario con rol de CLIENTE automáticamente
        $usuario = Usuario::create([
            'ci' => $validated['ci'],
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'telefono' => $validated['telefono'],
            'email' => $validated['email'],
            'direccion' => $validated['direccion'],
            'password' => Hash::make($validated['password']),
            'tipo_usuario' => 'CLIENTE', // Rol por defecto
            'estado' => 'ACTIVO',
        ]);

        // Iniciar sesión automáticamente
        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', '¡Cuenta creada exitosamente! Bienvenido a Trans Velasco.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
