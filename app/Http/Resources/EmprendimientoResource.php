<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmprendimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_emprendimiento' => $this->id_emprendimiento,
            'nombre_emprendimiento' => $this->nombre_emprendimiento,
            'tipo_emprendimiento' => $this->tipo_emprendimiento,
            'descripcion_emprendimiento' => $this->descripcion_emprendimiento,
            'slogan' => $this->slogan,
            'anio_inicio' => $this->anio_inicio,
            'numero_empleados' => $this->numero_empleados,
            'mobiliario' => $this->mobiliario,
            'signos_externos' => $this->signos_externos,
            
            // Incluimos TODAS las relaciones si han sido cargadas
            'proyeccion' => new ProyeccionResource($this->whenLoaded('proyeccion')),
            'sectores' => SectorResource::collection($this->whenLoaded('sectores')),
            'apoyos' => ApoyoResource::collection($this->whenLoaded('apoyos')),
            'ferias' => FeriaResource::collection($this->whenLoaded('ferias')),
            'formalizaciones' => FormalizacionResource::collection($this->whenLoaded('formalizaciones')),
            'necesidades' => NecesidadResource::collection($this->whenLoaded('necesidades')),
            'redes_sociales' => RedSocialResource::collection($this->whenLoaded('redesSociales')),
        ];
    }
}