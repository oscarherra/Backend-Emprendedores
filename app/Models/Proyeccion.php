<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyeccion extends Model
{
    use HasFactory;

    protected $table = 'proyeccion';
    protected $primaryKey = 'id_proyeccion';
    public $timestamps = false;

    protected $fillable = [
        'id_emprendimiento',
        'intereses',
        'ingreso_mensual',
    ];

    // Cast para el campo array de PostgreSQL
    protected $casts = [
        'intereses' => 'array',
    ];

    // Relación inversa: Una proyección pertenece a un emprendimiento
    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'id_emprendimiento', 'id_emprendimiento');
    }
}