<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $table = 'sector';
    protected $primaryKey = 'id_sector';
    public $timestamps = false;

    protected $fillable = ['nombre_sector'];

    public function emprendimientos()
    {
        return $this->belongsToMany(
            Emprendimiento::class,
            'emprendimiento_sector',
            'id_sector',
            'id_emprendimiento'
        );
    }

    public function subsectores()
    {
        return $this->hasMany(
            SubsectorCultural::class,
            'id_sector',
            'id_sector'
        );
    }
}
