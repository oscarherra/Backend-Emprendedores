<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feria extends Model
{
    protected $table = 'feria';
    protected $primaryKey = 'id_feria';
    public $timestamps = false;

    protected $fillable = ['nombre_feria'];

    public function emprendimientos()
    {
        return $this->belongsToMany(
            Emprendimiento::class,
            'emprendimiento_feria',
            'id_feria',
            'id_emprendimeinto' // en tu tabla el campo está con “m” de más
        );
    }
}
