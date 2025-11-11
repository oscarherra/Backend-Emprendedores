<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmprendedorCollection extends ResourceCollection
{
    public $collects = EmprendedorResource::class;

    public function toArray(Request $request): array
    {
        return ['data' => $this->collection];
    }
}