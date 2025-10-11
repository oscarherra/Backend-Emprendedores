<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmprendedorCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Simplemente devolvemos la colección de datos tal como viene.
        // El formato de cada emprendedor individual lo manejará EmprendedorResource.
        return [
            'data' => $this->collection,
        ];
    }
}