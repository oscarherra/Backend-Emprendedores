<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProyeccionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'intereses' => $this->intereses,
            'ingreso_mensual' => $this->ingreso_mensual,
        ];
    }
}