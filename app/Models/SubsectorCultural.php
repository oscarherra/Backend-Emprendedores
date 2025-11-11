<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubsectorCultural extends Model
{
    protected $table = 'subsector_cultural';
    protected $primaryKey = 'id_subsector';
    public $timestamps = false;

    protected $fillable = ['nombre', 'id_sector'];

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'id_sector', 'id_sector');
    }
}
