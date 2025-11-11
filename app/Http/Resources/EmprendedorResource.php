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

        // Si no confías en que todas las relaciones estén cargadas,
        // puedes dejar esto comentado y usamos defensas abajo.
        // $this->loadMissing([
        //     'emprendimientos.apoyos',
        //     'emprendimientos.ferias',
        //     'emprendimientos.formalizaciones',
        //     'emprendimientos.necesidades',
        //     'emprendimientos.redesSociales',
        //     'emprendimientos.sectores',
        //     'emprendimientos.proyeccion',
        // ]);

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
            'fecha_nacimiento' => $e->fecha_nacimiento,
            'edad'             => $e->fecha_nacimiento ? Carbon::parse($e->fecha_nacimiento)->age : null,
            'escolaridad'      => $e->escolaridad,     // cast array en el modelo
            'certificaciones'  => $e->certificaciones,

            // ===== Emprendimientos =====
            'emprendimientos' => $emps->map(function ($em) {
                // URLs públicas para imágenes/logo (disk public)
                $imgs = collect($em->imagenes_json ?? [])
                    ->filter()
                    ->map(fn ($p) => Storage::disk('public')->url($p))
                    ->values();

                $logo = $em->logo_path ? Storage::disk('public')->url($em->logo_path) : null;

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
