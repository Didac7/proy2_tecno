<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';
    protected $primaryKey = 'id_vehiculo';
    public $timestamps = false;

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'año',
        'capacidad_carga',
        'tipo_vehiculo',
        'id_chofer',
        'foto_url',
        'estado',
        'gps_activo',
        'latitud_actual',
        'longitud_actual',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'ultima_actualizacion_gps' => 'datetime',
        'gps_activo' => 'boolean',
    ];

    // Relaciones
    public function chofer()
    {
        return $this->belongsTo(Usuario::class, 'id_chofer', 'id_usuario');
    }

    public function paquetes()
    {
        return $this->hasMany(Paquete::class, 'id_vehiculo');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'id_vehiculo');
    }
}
