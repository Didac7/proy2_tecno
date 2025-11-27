<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';
    public $timestamps = false;

    protected $fillable = [
        'id_paquete',
        'id_cliente',
        'monto_total',
        'monto_pagado',
        'tipo_pago',
        'estado_pago',
        'metodo_pago',
        'numero_transaccion',
        'id_transaccion_pagofacil',
        'fecha_vencimiento',
        'fecha_pago',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'datetime',
    ];

    // Relaciones
    public function paquete()
    {
        return $this->belongsTo(Paquete::class, 'id_paquete', 'id_paquete');
    }

    public function cliente()
    {
        return $this->belongsTo(Usuario::class, 'id_cliente', 'id_usuario');
    }

    public function planPago()
    {
        return $this->hasOne(PlanPago::class, 'id_pago');
    }

    // Métodos auxiliares
    public function getSaldoPendiente()
    {
        return $this->monto_total - $this->monto_pagado;
    }

    public function estaPagado()
    {
        return $this->estado_pago === 'PAGADO';
    }
}
