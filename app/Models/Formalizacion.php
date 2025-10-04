<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formalizacion extends Model
{
    use HasFactory;

    protected $table = 'formalizacion';
    protected $primaryKey = 'id_formalizacion';
    public $timestamps = false;

    protected $fillable = [
        'tipo_formalizacion',
    ];

    public function emprendimientos()
    {
        return $this->belongsToMany(Emprendimiento::class, 'emprendimiento_formalizacion', 'id_formalizacion', 'id_emprendimiento');
    }
}