<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\Emprendedor;
use App\Models\Apoyo;
use App\Models\Feria;
use App\Models\Formalizacion;
use App\Models\Necesidad;
use App\Models\RedSocial;
use App\Models\Sector;

use App\Http\Resources\EmprendedorResource;
use App\Http\Resources\EmprendedorCollection;

class EmprendedorController extends Controller
{
    /**
     * GET /api/emprendedores (protegido)
     */
    public function index()
    {
        $emprendedores = Emprendedor::with([
            'emprendimientos' => function ($q) {
                $q->with(['apoyos','ferias','formalizaciones','necesidades','redesSociales','sectores','proyeccion']);
            }
        ])->orderBy('id_emprendedor', 'desc')->get();

        return new EmprendedorCollection($emprendedores);
    }

    /**
     * Utilidad: si llega string JSON o string simple, conviértelo a array.
     */
    private function toArray($value)
    {
        if (is_array($value)) return $value;

        if (is_string($value)) {
            $t = trim($value);
            if ($t === '') return [];
            if (($t[0] === '[' && str_ends_with($t, ']')) || ($t[0] === '{' && str_ends_with($t, '}'))) {
                try {
                    $d = json_decode($t, true, 512, JSON_THROW_ON_ERROR);
                    return is_array($d) ? $d : [];
                } catch (\Throwable $e) {
                    return [$value];
                }
            }
            return [$value];
        }

        return [];
    }

    /**
     * POST /api/emprendedores (público)
     */
    public function store(Request $request)
    {
        try {
            // Normalizaciones previas (si llegan como string)
            $request->merge([
                'escolaridad'     => $this->toArray($request->input('escolaridad', [])),
                'intereses'       => $this->toArray($request->input('intereses', [])),
                'formalizaciones' => $this->toArray($request->input('formalizaciones', [])),
                'apoyos'          => $this->toArray($request->input('apoyos', [])),
                'necesidades'     => $this->toArray($request->input('necesidades', [])),
                'redes'           => $this->toArray($request->input('redes', [])),
            ]);

            // ------------ VALIDACIÓN ------------
            $validator = Validator::make($request->all(), [
                // Paso 1 - Emprendedor
                'nombre'             => 'required|string|max:20',
                'apellido1'          => 'required|string|max:10',
                'apellido2'          => 'required|string|max:10',
                'cedula'             => 'required|string|max:12|unique:emprendedor,cedula',
                'fecha_nacimiento'   => 'required|date',
                'telefono'           => 'required|string|max:20',
                'correo_electronico' => 'nullable|email|max:50',
                'direccion'          => 'required|string',
                'distrito'           => 'required|string',
                'comunidad'          => 'required|string|max:50',
                'sexo'               => 'nullable|string|max:15',
                'escolaridad'        => 'required|array',
                'certificaciones'    => 'nullable|string',

                // Paso 2 - Emprendimiento (opcional/bloque mínimo si se envía)
                'nombre_emprendimiento'      => 'nullable|string|max:50',
                'tipo_emprendimiento'        => 'nullable|string|max:50',
                'descripcion_emprendimiento' => 'nullable|string',
                'slogan'                      => 'nullable|string',
                'anio_inicio'                 => 'nullable|integer|min:1900',
                'numero_empleados'            => 'nullable|integer|min:0',
                'mobiliario'                  => 'nullable|string',
                'signos_externos'             => 'nullable|string',

                // Proyección
                'intereses'       => 'nullable|array',
                'ingreso_mensual' => 'nullable|string|max:100',

                // Relaciones
                'formalizaciones' => 'nullable|array',
                'apoyos'          => 'nullable|array',
                'necesidades'     => 'nullable|array',

                // Sector
                'sector'          => 'nullable|string|max:100',

                // Redes
                'redes'           => 'nullable|array',

                // Archivos (solo imágenes; SIN logo)
                'imagenes'        => 'nullable',
                'imagenes.*'      => 'file|image|max:5120',
            ]);

            // Reglas condicionales si se envía algún campo de emprendimiento
            $algunoEmpr = $request->filled([
                'nombre_emprendimiento','tipo_emprendimiento','descripcion_emprendimiento',
                'slogan','anio_inicio','numero_empleados','mobiliario','signos_externos'
            ]);
            if ($algunoEmpr) {
                $validator->after(function($v) use ($request){
                    foreach (['nombre_emprendimiento','tipo_emprendimiento','descripcion_emprendimiento'] as $req) {
                        if (!$request->filled($req)) {
                            $v->errors()->add($req, 'Este campo es obligatorio cuando se envía información del emprendimiento.');
                        }
                    }
                });
            }

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // ------------ TRANSACCIÓN ------------
            $emprendedor = null;

            DB::transaction(function () use ($request, &$emprendedor, $algunoEmpr) {

                // 1) Emprendedor
                $emprendedor = Emprendedor::create([
                    'nombre'             => $request->nombre,
                    'apellido1'          => $request->apellido1,
                    'apellido2'          => $request->apellido2,
                    'cedula'             => $request->cedula,
                    'fecha_nacimiento'   => $request->fecha_nacimiento,
                    'telefono'           => $request->telefono,
                    'correo_electronico' => $request->correo_electronico,
                    'direccion'          => $request->direccion,
                    'distrito'           => $request->distrito,
                    'comunidad'          => $request->comunidad,
                    'sexo'               => $request->input('sexo'),
                    'escolaridad'        => $request->input('escolaridad', []),
                    'certificaciones'    => $request->input('certificaciones'),
                ]);

                // 2) Emprendimiento (si se envió)
                if ($algunoEmpr || $request->filled('nombre_emprendimiento')) {

                    $emprendimiento = $emprendedor->emprendimientos()->create([
                        'nombre_emprendimiento'      => $request->input('nombre_emprendimiento'),
                        'tipo_emprendimiento'        => $request->input('tipo_emprendimiento'),
                        'descripcion_emprendimiento' => $request->input('descripcion_emprendimiento'),
                        'slogan'                      => $request->input('slogan'),
                        'anio_inicio'                 => $request->input('anio_inicio'),
                        'numero_empleados'            => $request->input('numero_empleados'),
                        'mobiliario'                  => $request->input('mobiliario'),
                        'signos_externos'             => $request->input('signos_externos'),
                        // Logo deshabilitado; dejar explícito
                        'tiene_logo'                  => false,
                        'logo_path'                   => null,
                        // Extras
                        'sector_text'                 => $request->input('sector'),
                        'participo_feria'             => $request->input('participo_feria') === 'Si',
                        'cuales_ferias'               => $request->input('cuales_ferias'),
                    ]);

                    // 2.1 Proyección
                    if ($request->hasAny(['intereses', 'ingreso_mensual'])) {
                        $emprendimiento->proyeccion()->create([
                            'intereses'       => $request->input('intereses', []),
                            'ingreso_mensual' => $request->input('ingreso_mensual'),
                        ]);
                    }

                    // 2.2 Sector (catálogo + pivot)
                    if ($request->filled('sector')) {
                        $sector = Sector::firstOrCreate(['nombre_sector' => $request->sector]);
                        $emprendimiento->sectores()->sync([$sector->id_sector]);
                    }

                    // 2.3 Formalizaciones
                    if (!empty($request->formalizaciones)) {
                        $formalizacionIds = Formalizacion::whereIn('tipo_formalizacion', (array)$request->formalizaciones)
                            ->pluck('id_formalizacion');
                        $emprendimiento->formalizaciones()->sync($formalizacionIds);
                    }

                    // 2.4 Apoyos
                    if (!empty($request->apoyos)) {
                        $apoyoIds = Apoyo::whereIn('tipo_apoyo', (array)$request->apoyos)
                            ->pluck('id_apoyo');
                        $emprendimiento->apoyos()->sync($apoyoIds);
                    }

                    // 2.5 Necesidades (crea si no existe)
                    if (!empty($request->necesidades) && !empty($request->necesidades[0])) {
                        $necesidad = Necesidad::firstOrCreate(['descripcion_necesidad' => $request->necesidades[0]]);
                        $emprendimiento->necesidades()->sync([$necesidad->id_necesidad]);
                    }

                    // 2.6 Redes sociales (pivot con url)
                    $redes = $request->input('redes', []);
                    if (!is_array($redes)) $redes = [];
                    foreach (['facebook','instagram','tiktok','website','whatsapp'] as $k) {
                        if (!$request->has("redes.$k") && $request->filled($k)) {
                            $redes[$k] = $request->input($k);
                        }
                    }
                    if ($redes) {
                        $attach = [];
                        foreach ($redes as $nombre => $url) {
                            if (!$url) continue;
                            $rs = RedSocial::firstOrCreate(['redes_sociales' => ucfirst($nombre)]);
                            $attach[$rs->id_red_social] = ['url_usuario' => $url];
                        }
                        if ($attach) {
                            $emprendimiento->redesSociales()->syncWithoutDetaching($attach);
                        }
                    }

                    // 2.7 SOLO IMÁGENES (sin logo)
                    $paths = [];
                    if ($request->hasFile('imagenes')) {
                        foreach ((array) $request->file('imagenes') as $file) {
                            if (!$file) continue;
                            $paths[] = $file->store('emprendimientos', 'public');
                        }
                    }
                    $emprendimiento->imagenes_json = $paths ?: null; // jsonb en BD
                    $emprendimiento->save();
                }
            });

            // Carga relaciones para la respuesta
            return response()->json(
                new EmprendedorResource(
                    $emprendedor->load([
                        'emprendimientos.apoyos',
                        'emprendimientos.ferias',
                        'emprendimientos.formalizaciones',
                        'emprendimientos.necesidades',
                        'emprendimientos.redesSociales',
                        'emprendimientos.sectores',
                        'emprendimientos.proyeccion'
                    ])
                ),
                201
            );
        } catch (\Throwable $e) {
            Log::error('Error al guardar emprendedor', [
                'msg'     => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => substr($e->getTraceAsString(), 0, 2000),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al guardar el registro.',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/emprendedores/{emprendedor} (protegido)
     */
    public function show(Emprendedor $emprendedor)
    {
        return new EmprendedorResource(
            $emprendedor->load([
                'emprendimientos.apoyos',
                'emprendimientos.ferias',
                'emprendimientos.formalizaciones',
                'emprendimientos.necesidades',
                'emprendimientos.redesSociales',
                'emprendimientos.sectores',
                'emprendimientos.proyeccion'
            ])
        );
    }

    /**
     * PUT /api/emprendedores/{emprendedor} (protegido)
     */
    public function update(Request $request, Emprendedor $emprendedor)
    {
        $validator = Validator::make($request->all(), [
            'cedula' => [
                'required',
                'string',
                'max:12',
                Rule::unique('emprendedor', 'cedula')->ignore($emprendedor->id_emprendedor, 'id_emprendedor')
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $emprendedor->update($request->all());

        return new EmprendedorResource(
            $emprendedor->load(['emprendimientos'])
        );
    }

    /**
     * DELETE /api/emprendedores/{emprendedor} (protegido)
     */
    public function destroy(Emprendedor $emprendedor)
    {
        DB::transaction(function () use ($emprendedor) {
            foreach ($emprendedor->emprendimientos as $emprendimiento) {
                $emprendimiento->apoyos()->detach();
                $emprendimiento->ferias()->detach();
                $emprendimiento->formalizaciones()->detach();
                $emprendimiento->necesidades()->detach();
                $emprendimiento->redesSociales()->detach();
                $emprendimiento->sectores()->detach();

                if ($emprendimiento->proyeccion) {
                    $emprendimiento->proyeccion->delete();
                }
            }

            $emprendedor->emprendimientos()->delete();
            $emprendedor->delete();
        });

        return response()->json(null, 204);
    }
}
