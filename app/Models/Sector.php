<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $table = 'sector';
    protected $primaryKey = 'id_sector';
    public $timestamps = false;

    protected $fillable = [
        'nombre_sector',
    ];
    
    // Relación: Un sector tiene muchos subsectores
    public function subsectores()
    {
        return $this->hasMany(SubsectorCultural::class, 'id_sector', 'id_sector');
    }

    public function emprendimientos()
    {
        return $this->belongsToMany(Emprendimiento::class, 'emprendimiento_sector', 'id_sector', 'id_emprendimiento');
    }
}