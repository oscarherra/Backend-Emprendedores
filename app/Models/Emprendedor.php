<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprendedor extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'emprendedor';

    // Clave primaria
    protected $primaryKey = 'id_emprendedor';

    // Desactivar timestamps si no tienes created_at y updated_at
    public $timestamps = false;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre',
        'apellido1',
        'apellido2',
        'cedula',
        'fecha_nacimiento',
        'telefono',
        'correo_electronico',
        'direccion',
        'distrito',
        'comunidad',
        'escolaridad',
        'certificaciones',
    ];

    // Cast para el campo array de PostgreSQL
    protected $casts = [
        'escolaridad' => 'array',
    ];

    // Relación: Un emprendedor tiene muchos emprendimientos
    public function emprendimientos()
    {
        return $this->hasMany(Emprendimiento::class, 'id_emprendedor', 'id_emprendedor');
    }
}