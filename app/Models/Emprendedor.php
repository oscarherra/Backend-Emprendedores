<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emprendedor extends Model
{
    // Nombre de la tabla
    protected $table = 'emprendedor';

    // Clave primaria personalizada
    protected $primaryKey = 'id_emprendedor';

    // Sin timestamps (no hay created_at / updated_at en tu DB)
    public $timestamps = false;

    // Campos que se pueden asignar en masa
    protected $fillable = [
  'nombre','apellido1','apellido2','cedula','fecha_nacimiento','telefono',
  'correo_electronico','direccion','distrito','comunidad','sexo',
  'escolaridad','certificaciones'
];

    // Convierte automáticamente JSON <-> array
    protected $casts = [
  'escolaridad' => 'array',
  'fecha_nacimiento' => 'date',
];

    /**
     * Relación 1:N -> Emprendimientos
     * Un emprendedor puede tener varios emprendimientos.
     */
    public function emprendimientos() { return $this->hasMany(Emprendimiento::class, 'id_emprendedor'); }

}
