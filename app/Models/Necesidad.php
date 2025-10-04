<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Necesidad extends Model
{
    use HasFactory;

    protected $table = 'necesidad';
    protected $primaryKey = 'id_necesidad';
    public $timestamps = false;

    protected $fillable = [
        'descripcion_necesidad',
    ];

    public function emprendimientos()
    {
        return $this->belongsToMany(Emprendimiento::class, 'emprendimiento_necesidad', 'id_necesidad', 'id_emprendimiento');
    }
}