<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Propietario
        Usuario::create([
            'ci' => '1234567',
            'nombre' => 'Juan',
            'apellido' => 'Velasco',
            'tipo_usuario' => 'PROPIETARIO',
            'telefono' => '70123456',
            'email' => 'propietario@transvelasco.com',
            'password' => Hash::make('password'),
            'direccion' => 'Av. Principal #123',
            'estado' => 'ACTIVO',
        ]);

        // Secretaria
        Usuario::create([
            'ci' => '2345678',
            'nombre' => 'María',
            'apellido' => 'García',
            'tipo_usuario' => 'SECRETARIA',
            'telefono' => '70234567',
            'email' => 'secretaria@transvelasco.com',
            'password' => Hash::make('password'),
            'direccion' => 'Calle Secundaria #456',
            'estado' => 'ACTIVO',
        ]);

        // Chofer
        Usuario::create([
            'ci' => '3456789',
            'nombre' => 'Carlos',
            'apellido' => 'Rodríguez',
            'tipo_usuario' => 'CHOFER',
            'telefono' => '70345678',
            'email' => 'chofer@transvelasco.com',
            'password' => Hash::make('password'),
            'direccion' => 'Zona Norte #789',
            'estado' => 'ACTIVO',
        ]);

        // Cliente
        Usuario::create([
            'ci' => '4567890',
            'nombre' => 'Ana',
            'apellido' => 'López',
            'tipo_usuario' => 'CLIENTE',
            'telefono' => '70456789',
            'email' => 'cliente@transvelasco.com',
            'password' => Hash::make('password'),
            'direccion' => 'Zona Sur #321',
            'estado' => 'ACTIVO',
        ]);

        // Más clientes de prueba
        Usuario::create([
            'ci' => '5678901',
            'nombre' => 'Pedro',
            'apellido' => 'Martínez',
            'tipo_usuario' => 'CLIENTE',
            'telefono' => '70567890',
            'email' => 'pedro.martinez@email.com',
            'password' => Hash::make('password'),
            'direccion' => 'Av. Libertador #555',
            'estado' => 'ACTIVO',
        ]);
    }
}
