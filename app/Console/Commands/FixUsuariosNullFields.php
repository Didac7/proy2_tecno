<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;

class FixUsuariosNullFields extends Command
{
    protected $signature = 'fix:usuarios-null';
    protected $description = 'Actualizar usuarios con campos null';

    public function handle()
    {
        $this->info('Actualizando usuarios con campos null...');
        
        $usuarios = Usuario::all();
        $actualizados = 0;
        
        foreach ($usuarios as $usuario) {
            $cambios = [];
            
            if (empty($usuario->ci)) {
                $cambios['ci'] = '0000000';
            }
            
            if (empty($usuario->telefono)) {
                $cambios['telefono'] = '72971922';
            }
            
            if (empty($usuario->email)) {
                $cambios['email'] = 'usuario' . $usuario->id_usuario . '@transvelasco.com';
            }
            
            if (!empty($cambios)) {
                $usuario->update($cambios);
                $this->line("Usuario #{$usuario->id_usuario} ({$usuario->nombre}): " . implode(', ', array_keys($cambios)));
                $actualizados++;
            }
        }
        
        if ($actualizados > 0) {
            $this->info("\n✓ Se actualizaron {$actualizados} usuario(s)");
        } else {
            $this->info("\n✓ No hay usuarios con campos null");
        }
        
        return 0;
    }
}
