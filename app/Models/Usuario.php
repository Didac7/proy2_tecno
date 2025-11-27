<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'ci',
        'nombre',
        'apellido',
        'tipo_usuario',
        'telefono',
        'email',
        'direccion',
        'password',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    // Relaciones
    public function paquetes()
    {
        return $this->hasMany(Paquete::class, 'id_cliente');
    }

    public function vehiculo()
    {
        return $this->hasOne(Vehiculo::class, 'id_chofer');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_cliente');
    }

    // Verificar roles
    public function isCliente()
    {
        return $this->tipo_usuario === 'CLIENTE';
    }

    public function isChofer()
    {
        return $this->tipo_usuario === 'CHOFER';
    }

    public function paquetesComoDestinatario()
    {
        return $this->hasMany(Paquete::class, 'id_destinatario', 'id_usuario');
    }

    public function paquetesComoRemitente()
    {
        return $this->hasMany(Paquete::class, 'id_remitente', 'id_usuario');
    }

    public function isSecretaria()
    {
        return $this->tipo_usuario === 'SECRETARIA';
    }

    public function isPropietario()
    {
        return $this->tipo_usuario === 'PROPIETARIO';
    }

    public function hasRole($role)
    {
        return $this->tipo_usuario === strtoupper($role);
    }
}
