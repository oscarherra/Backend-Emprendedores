<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubsectorCultural extends Model
{
    use HasFactory;

    protected $table = 'subsector_cultural';
    protected $primaryKey = 'id_subsector';
    public $timestamps = false;

    protected $fillable = [
        'id_sector',
        'nombre',
    ];

    // Relación inversa: Un subsector pertenece a un sector
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'id_sector', 'id_sector');
    }
}