<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dashboard (solo propietario y secretaria)
        Menu::create([
            'nombre' => 'Dashboard',
            'icono' => '📊',
            'url' => '/dashboard',
            'orden' => 1,
            'roles' => json_encode(['PROPIETARIO', 'SECRETARIA']),
            'activo' => true,
        ]);

        // Usuarios (Menú padre)
        $menuUsuarios = Menu::create([
            'nombre' => 'Usuarios',
            'icono' => '👥',
            'url' => '#',
            'orden' => 2,
            'roles' => json_encode(['PROPIETARIO', 'SECRETARIA']),
            'activo' => true,
        ]);

        // Submenús de Usuarios
        $submenus = [
            ['nombre' => 'Todos', 'icono' => '👥', 'url' => '/usuarios', 'orden' => 1],
            ['nombre' => 'Clientes', 'icono' => '👤', 'url' => '/usuarios?tipo_usuario=CLIENTE', 'orden' => 2],
            ['nombre' => 'Choferes', 'icono' => '🚚', 'url' => '/usuarios?tipo_usuario=CHOFER', 'orden' => 3],
            ['nombre' => 'Secretarias', 'icono' => '👩‍💼', 'url' => '/usuarios?tipo_usuario=SECRETARIA', 'orden' => 4],
            ['nombre' => 'Propietarios', 'icono' => '👔', 'url' => '/usuarios?tipo_usuario=PROPIETARIO', 'orden' => 5],
        ];

        foreach ($submenus as $submenu) {
            Menu::create([
                'nombre' => $submenu['nombre'],
                'icono' => $submenu['icono'],
                'url' => $submenu['url'],
                'orden' => $submenu['orden'],
                'padre_id' => $menuUsuarios->id_menu,
                'roles' => json_encode(['PROPIETARIO', 'SECRETARIA']),
                'activo' => true,
            ]);
        }

        // Vehículos (secretaria, propietario)
        Menu::create([
            'nombre' => 'Vehículos',
            'icono' => '🚗',
            'url' => '/vehiculos',
            'orden' => 3,
            'roles' => json_encode(['PROPIETARIO', 'SECRETARIA']),
            'activo' => true,
        ]);

        // Destinos (secretaria y propietario)
        Menu::create([
            'nombre' => 'Destinos',
            'icono' => '📍',
            'url' => '/destinos',
            'orden' => 4,
            'roles' => json_encode(['PROPIETARIO', 'SECRETARIA']),
            'activo' => true,
        ]);

        // Rutas (secretaria y propietario)
        Menu::create([
            'nombre' => 'Rutas',
            'icono' => '🗺️',
            'url' => '/rutas',
            'orden' => 5,
            'roles' => json_encode(['PROPIETARIO', 'SECRETARIA']),
            'activo' => true,
        ]);

        // Paquetes (todos excepto clientes - clientes ven sus paquetes, choferes los de su vehículo)
        Menu::create([
            'nombre' => 'Paquetes',
            'icono' => '📦',
            'url' => '/paquetes',
            'orden' => 6,
            'roles' => json_encode(['PROPIETARIO', 'SECRETARIA', 'CHOFER', 'CLIENTE']),
            'activo' => true,
        ]);

        // Rastreo (solo clientes)
        Menu::create([
            'nombre' => 'Rastreo',
            'icono' => '🔍',
            'url' => '/rastreo',
            'orden' => 7,
            'roles' => json_encode(['CLIENTE']),
            'activo' => true,
        ]);

        // Pagos (solo propietario y secretaria, NO clientes)
        Menu::create([
            'nombre' => 'Pagos',
            'icono' => '💳',
            'url' => '/pagos',
            'orden' => 7,
            'roles' => json_encode(['PROPIETARIO', 'SECRETARIA']),
            'activo' => true,
        ]);

        // Reportes (solo secretaria y propietario)
        Menu::create([
            'nombre' => 'Reportes',
            'icono' => '📈',
            'url' => '/reportes',
            'orden' => 9,
            'roles' => json_encode(['PROPIETARIO', 'SECRETARIA']),
            'activo' => true,
        ]);
    }
}
