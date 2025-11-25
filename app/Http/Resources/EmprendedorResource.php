<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmprendedorResource extends JsonResource
{
    /**
     * Transforma el recurso a arreglo JSON.
     */
    public function toArray(Request $request): array
    {
        $e = $this->resource;

        // Emprendimientos (si no está cargado devolvemos colección vacía segura)
        $emps = $e->relationLoaded('emprendimientos')
            ? $e->emprendimientos
            : ($e->emprendimientos ?? collect());

        return [
            // ===== Emprendedor =====
            'id_emprendedor'   => $e->id_emprendedor,
            'nombre'           => $e->nombre,
            'apellido1'        => $e->apellido1,
            'apellido2'        => $e->apellido2,
            'nombre_completo'  => trim(($e->nombre ?? '').' '.($e->apellido1 ?? '').' '.($e->apellido2 ?? '')),
            'cedula'           => $e->cedula,
            'correo_electronico' => $e->correo_electronico,
            'telefono'         => $e->telefono,
            'direccion'        => $e->direccion,
            'distrito'         => $e->distrito,
            'comunidad'        => $e->comunidad,
            'sexo'             => $e->sexo,

            // ⇢ Fecha formateada (YYYY-MM-DD). Cambia a ->format('d/m/Y') si prefieres latino.
            'fecha_nacimiento' => $e->fecha_nacimiento
                ? Carbon::parse($e->fecha_nacimiento)->toDateString() // "YYYY-MM-DD"
                : null,

            'edad'             => $e->fecha_nacimiento ? Carbon::parse($e->fecha_nacimiento)->age : null,
            'escolaridad'      => $e->escolaridad,     // cast array en el modelo
            'certificaciones'  => $e->certificaciones,

            // ===== Emprendimientos =====
            'emprendimientos' => $emps->map(function ($em) {
                // URLs públicas ABSOLUTAS para imágenes/logo (disk public)
                $imgs = collect($em->imagenes_json ?? [])
                    ->filter()
                    ->map(fn ($p) => url(Storage::disk('public')->url($p)))  // http://127.0.0.1:8000/storage/...
                    ->values();

                $logo = $em->logo_path
                    ? url(Storage::disk('public')->url($em->logo_path))
                    : null;

                // Relaciones opcionales (defensas si no existen)
                $apoyos          = method_exists($em, 'apoyos')
                    ? $em->apoyos->pluck('tipo_apoyo')->values()
                    : collect();

                $formalizaciones = method_exists($em, 'formalizaciones')
                    ? $em->formalizaciones->pluck('tipo_formalizacion')->values()
                    : collect();

                $necesidades     = method_exists($em, 'necesidades')
                    ? $em->necesidades->pluck('descripcion_necesidad')->values()
                    : collect();

                $sectores        = method_exists($em, 'sectores')
                    ? $em->sectores->pluck('nombre_sector')->values()
                    : collect();

                $redes = method_exists($em, 'redesSociales')
                    ? $em->redesSociales->map(function ($rs) {
                        return [
                            'nombre' => $rs->redes_sociales,
                            'url'    => $rs->pivot->url_usuario,
                        ];
                    })->values()
                    : collect();

                return [
                    'id_emprendimiento' => $em->id_emprendimiento,
                    'nombre'            => $em->nombre_emprendimiento,
                    'tipo'              => $em->tipo_emprendimiento,
                    'descripcion'       => $em->descripcion_emprendimiento,
                    'slogan'            => $em->slogan,
                    'anio_inicio'       => $em->anio_inicio,
                    'numero_empleados'  => $em->numero_empleados,
                    'mobiliario'        => $em->mobiliario,
                    'signos_externos'   => $em->signos_externos,

                    // Logo / Imágenes
                    'tiene_logo'        => (bool) $em->tiene_logo,
                    'logo_url'          => $logo,
                    'imagenes'          => $imgs,

                    // Sector (texto rápido + relación)
                    'sector'            => $em->sector_text,
                    'sectores'          => $sectores,

                    // Ferias
                    'participo_feria'   => (bool) $em->participo_feria,
                    'cuales_ferias'     => $em->cuales_ferias,

                    // Proyección
                    'proyeccion'        => $em->proyeccion ? [
                        'intereses'       => $em->proyeccion->intereses,
                        'ingreso_mensual' => $em->proyeccion->ingreso_mensual,
                    ] : null,

                    // M:N
                    'apoyos'            => $apoyos,
                    'formalizaciones'   => $formalizaciones,
                    'necesidades'       => $necesidades,

                    // Redes sociales
                    'redes'             => $redes,
                ];
            })->values(),
        ];
    }
}
