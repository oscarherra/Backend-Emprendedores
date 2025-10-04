<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprendimiento extends Model
{
    use HasFactory;

    protected $table = 'emprendimiento';
    protected $primaryKey = 'id_emprendimiento';
    public $timestamps = false;

    protected $fillable = [
        'nombre_emprendimiento',
        'tipo_emprendimiento',
        'descripcion_emprendimiento',
        'imagenes', // ¡Ojo con este campo! Ver nota al final
        'logo',     // ¡Ojo con este campo! Ver nota al final
        'slogan',
        'anio_inicio',
        'numero_empleados',
        'mobiliario',
        'signos_externos',
        'id_emprendedor',
    ];

    // Relación inversa: Un emprendimiento pertenece a un emprendedor
    public function emprendedor()
    {
        return $this->belongsTo(Emprendedor::class, 'id_emprendedor', 'id_emprendedor');
    }

    // Relación uno a uno: Un emprendimiento tiene una proyección
    public function proyeccion()
    {
        return $this->hasOne(Proyeccion::class, 'id_emprendimiento', 'id_emprendimiento');
    }
    
    // --- RELACIONES MUCHOS A MUCHOS ---

    public function apoyos()
    {
        return $this->belongsToMany(Apoyo::class, 'emprendimiento_apoyo', 'id_emprendimiento', 'id_apoyo');
    }

    public function ferias()
    {
        // Ojo: tu columna se llama 'id_emprendimeinto' (con error de tipeo)
        // Laravel espera 'id_emprendimiento', así que lo especificamos manualmente.
        return $this->belongsToMany(Feria::class, 'emprendimiento_feria', 'id_emprendimeinto', 'id_feria');
    }

    public function formalizaciones()
    {
        return $this->belongsToMany(Formalizacion::class, 'emprendimiento_formalizacion', 'id_emprendimiento', 'id_formalizacion');
    }

    public function necesidades()
    {
        return $this->belongsToMany(Necesidad::class, 'emprendimiento_necesidad', 'id_emprendimiento', 'id_necesidad');
    }
    
    public function sectores()
    {
        return $this->belongsToMany(Sector::class, 'emprendimiento_sector', 'id_emprendimiento', 'id_sector');
    }
    
    public function redesSociales()
    {
        // Esta relación tiene un campo extra en la tabla pivote
        return $this->belongsToMany(RedSocial::class, 'emprendimiento_red', 'id_emprendimiento', 'id_red_social')
                    ->withPivot('url_usuario');
    }
}