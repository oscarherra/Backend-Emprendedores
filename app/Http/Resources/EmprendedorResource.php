<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmprendedorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // --- Datos del Emprendedor (TODOS) ---
            'id_emprendedor' => $this->id_emprendedor,
            'nombre_completo' => $this->nombre . ' ' . $this->apellido1 . ' ' . $this->apellido2,
            'nombre' => $this->nombre,
            'apellido1' => $this->apellido1,
            'apellido2' => $this->apellido2,
            'cedula' => $this->cedula,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'telefono' => $this->telefono,
            'correo_electronico' => $this->correo_electronico,
            'direccion' => $this->direccion,
            'distrito' => $this->distrito,
            'comunidad' => $this->comunidad,
            'escolaridad' => $this->escolaridad,
            'certificaciones' => $this->certificaciones,
            // (Si tenías 'sexo' y 'edad' en la tabla, también irían aquí)
            // 'sexo' => $this->sexo,
            // 'edad' => $this->edad,

            // --- Datos del Emprendimiento (Anidados) ---
            'emprendimiento' => new EmprendimientoResource($this->whenLoaded('emprendimientos')->first()),
        ];
    }
}