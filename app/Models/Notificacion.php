<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion';

    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    // Métodos auxiliares
    public function marcarComoLeida()
    {
        $this->update(['leida' => true]);
    }

    public static function crearNotificacion($idUsuario, $titulo, $mensaje, $tipo = 'INFO')
    {
        return self::create([
            'id_usuario' => $idUsuario,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
        ]);
    }
}
