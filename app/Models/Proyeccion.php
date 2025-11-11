<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyeccion extends Model
{
    // Nombre de la tabla
    protected $table = 'proyeccion';

    // Clave primaria personalizada
    protected $primaryKey = 'id_proyeccion';

    // Sin timestamps
    public $timestamps = false;

    // Campos asignables
    protected $fillable = [
        'id_emprendimiento',
        'intereses',
        'ingreso_mensual',
    ];

    // Convierte automáticamente JSON <-> array
    protected $casts = [
        'intereses' => 'array',
    ];

    /**
     * Relación inversa 1:1 con Emprendimiento
     */
    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'id_emprendimiento', 'id_emprendimiento');
    }
}
