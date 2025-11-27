<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitaPagina extends Model
{
    use HasFactory;

    protected $table = 'visitas_pagina';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'pagina',
        'contador',
    ];

    // Incrementar contador
    public static function incrementar($pagina)
    {
        $visita = self::firstOrCreate(['pagina' => $pagina]);
        $visita->increment('contador');
        return $visita->contador;
    }
}
