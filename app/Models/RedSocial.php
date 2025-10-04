<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedSocial extends Model
{
    use HasFactory;

    protected $table = 'red_social';
    protected $primaryKey = 'id_red_social';
    public $timestamps = false;

    protected $fillable = [
        'redes_sociales',
    ];

    public function emprendimientos()
    {
        return $this->belongsToMany(Emprendimiento::class, 'emprendimiento_red', 'id_red_social', 'id_emprendimiento')
                    ->withPivot('url_usuario');
    }
}