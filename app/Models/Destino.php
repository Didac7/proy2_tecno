<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destino extends Model
{
    use HasFactory;

    protected $table = 'destinos';
    protected $primaryKey = 'id_destino';
    public $timestamps = false;

    protected $fillable = [
        'nombre_destino',
        'direccion',
        'ciudad',
        'departamento',
        'pais',
        'latitud',
        'longitud',
        'descripcion',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    // Relaciones
    public function rutasOrigen()
    {
        return $this->hasMany(Ruta::class, 'id_origen');
    }

    public function rutasDestino()
    {
        return $this->hasMany(Ruta::class, 'id_destino');
    }
}
