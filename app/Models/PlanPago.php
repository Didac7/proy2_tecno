<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPago extends Model
{
    use HasFactory;

    protected $table = 'planes_pago';
    protected $primaryKey = 'id_plan';
    public $timestamps = false;

    protected $fillable = [
        'id_pago',
        'numero_cuotas',
        'monto_cuota',
        'cuotas_pagadas',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relaciones
    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'id_plan', 'id_plan')->orderBy('numero_cuota');
    }

    // Métodos helper
    public function cuotasPendientes()
    {
        return $this->cuotas()->where('estado', 'PENDIENTE');
    }

    public function cuotasPagadas()
    {
        return $this->cuotas()->where('estado', 'PAGADA');
    }
}
