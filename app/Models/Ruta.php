<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'rutas';
    protected $primaryKey = 'id_ruta';
    public $timestamps = false;

    protected $fillable = [
        'nombre_ruta',
        'id_origen',
        'id_destino',
        'distancia_km',
        'tiempo_estimado_horas',
        'costo_base',
        'descripcion_ruta',
        'estado',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    // Relaciones
    public function origen()
    {
        return $this->belongsTo(Destino::class, 'id_origen', 'id_destino');
    }

    public function destino()
    {
        return $this->belongsTo(Destino::class, 'id_destino', 'id_destino');
    }

    public function paquetes()
    {
        return $this->hasMany(Paquete::class, 'id_ruta');
    }
}
