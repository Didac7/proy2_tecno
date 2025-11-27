<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    use HasFactory;

    protected $table = 'paquetes';
    protected $primaryKey = 'id_paquete';
    public $timestamps = false;

    protected $fillable = [
        'codigo_seguimiento',
        'id_remitente',
        'id_destinatario',
        'id_ruta',
        'id_vehiculo',
        'descripcion_contenido',
        'precio',
        'tipo_paquete',
        'prioridad',
        'estado_paquete',
        'direccion_recogida',
        'direccion_entrega',
        'nombre_destinatario',
        'telefono_destinatario',
        'fecha_entrega_estimada',
        'fecha_entrega_real',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'fecha_entrega_estimada' => 'datetime',
        'fecha_entrega_real' => 'datetime',
    ];

    // Relaciones
    public function remitente()
    {
        return $this->belongsTo(Usuario::class, 'id_remitente', 'id_usuario');
    }

    public function destinatario()
    {
        return $this->belongsTo(Usuario::class, 'id_destinatario', 'id_usuario');
    }

    // Alias para compatibilidad
    public function cliente()
    {
        return $this->destinatario();
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta', 'id_ruta');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo', 'id_vehiculo');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'id_paquete');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'id_paquete');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_paquete');
    }
}
