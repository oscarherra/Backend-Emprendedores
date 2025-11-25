<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emprendimiento extends Model
{
    protected $table = 'emprendimiento';
    protected $primaryKey = 'id_emprendimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_emprendedor',
        'nombre_emprendimiento',
        'tipo_emprendimiento',
        'descripcion_emprendimiento',
        'slogan',
        'anio_inicio',
        'numero_empleados',
        'mobiliario',
        'signos_externos',

        'tiene_logo',
        'sector_text',
        'participo_feria',
        'cuales_ferias',
        'logo_path',
        'imagenes_json',
    ];

    protected $casts = [
        'imagenes_json'   => 'array',
        'tiene_logo'      => 'boolean',
        'participo_feria' => 'boolean',
    ];


    public function emprendedor()
    {
        return $this->belongsTo(Emprendedor::class, 'id_emprendedor', 'id_emprendedor');
    }


    public function apoyos()
    {
        return $this->belongsToMany(
            Apoyo::class,
            'emprendimiento_apoyo',
            'id_emprendimiento',
            'id_apoyo'
        );
    }

    public function ferias()
    {
        return $this->belongsToMany(
            Feria::class,
            'emprendimiento_feria',
            'id_emprendimeinto', 
            'id_feria'
        );
    }

    public function formalizaciones()
    {
        return $this->belongsToMany(
            Formalizacion::class,
            'emprendimiento_formalizacion',
            'id_emprendimiento',
            'id_formalizacion'
        );
    }

    public function necesidades()
    {
        return $this->belongsToMany(
            Necesidad::class,
            'emprendimiento_necesidad',
            'id_emprendimiento',
            'id_necesidad'
        );
    }

    public function redesSociales()
    {
        return $this->belongsToMany(
            RedSocial::class,
            'emprendimiento_red',
            'id_emprendimiento',
            'id_red_social'
        )->withPivot('url_usuario');
    }

    public function sectores()
    {
        return $this->belongsToMany(
            Sector::class,
            'emprendimiento_sector',
            'id_emprendimiento',
            'id_sector'
        );
    }

    // Relación 1:1 con Proyección
    public function proyeccion()
    {
        return $this->hasOne(Proyeccion::class, 'id_emprendimiento', 'id_emprendimiento');
    }
}
