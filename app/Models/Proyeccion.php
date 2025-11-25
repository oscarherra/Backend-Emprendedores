<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyeccion extends Model
{
    // Nombre de la tabla
    protected $table = 'proyeccion';

    // Clave primaria personalizada
    protected $primaryKey = 'id_proyeccion';


    public $timestamps = false;


    protected $fillable = [
        'id_emprendimiento',
        'intereses',
        'ingreso_mensual',
    ];

    // Convierte automáticamente JSON <-> array
    protected $casts = [
        'intereses' => 'array',
    ];

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'id_emprendimiento', 'id_emprendimiento');
    }
}
