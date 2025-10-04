<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apoyo extends Model
{
    use HasFactory;

    protected $table = 'apoyo';
    protected $primaryKey = 'id_apoyo';
    public $timestamps = false;

    protected $fillable = [
        'tipo_apoyo',
    ];

    public function emprendimientos()
    {
        return $this->belongsToMany(Emprendimiento::class, 'emprendimiento_apoyo', 'id_apoyo', 'id_emprendimiento');
    }
}