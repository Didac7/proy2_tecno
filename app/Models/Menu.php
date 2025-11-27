<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';
    protected $primaryKey = 'id_menu';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'icono',
        'url',
        'orden',
        'padre_id',
        'roles',
        'activo',
    ];

    protected $casts = [
        'roles' => 'array',
        'activo' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relaciones
    public function padre()
    {
        return $this->belongsTo(Menu::class, 'padre_id', 'id_menu');
    }

    public function hijos()
    {
        return $this->hasMany(Menu::class, 'padre_id', 'id_menu')->orderBy('orden');
    }

    // Scope para menú activo
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // Scope para menú por rol
    public function scopeParaRol($query, $rol)
    {
        return $query->where(function($q) use ($rol) {
            $q->whereJsonContains('roles', $rol)
              ->orWhereNull('roles');
        });
    }
}
