<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feria extends Model
{
    use HasFactory;

    protected $table = 'feria';
    protected $primaryKey = 'id_feria';
    public $timestamps = false;

    protected $fillable = [
        'nombre_feria',
    ];

    public function emprendimientos()
    {
        // Ojo: tu columna se llama 'id_emprendimeinto' (con error de tipeo)
        return $this->belongsToMany(Emprendimiento::class, 'emprendimiento_feria', 'id_feria', 'id_emprendimeinto');
    }
}