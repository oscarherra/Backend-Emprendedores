<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// ===================================================================
//  DECLARACIONES 'use' ESENCIALES
//  Estas líneas importan los modelos y "traductores" (Resources)
//  que el controlador necesita para funcionar correctamente.
// ===================================================================
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
     * Muestra una lista de todos los emprendedores.
     * Usado por: la tabla principal de tu dashboard.
     * Ruta: GET /api/emprendedores
     */
    public function index()
    {
        // Usamos with('emprendimientos') para pre-cargar los datos relacionados
        // y ser más eficientes. Ordenamos por el más reciente.
        $emprendedores = Emprendedor::with('emprendimientos')->orderBy('id_emprendedor', 'desc')->get();

        // Devolvemos los datos a través de una Colección de Resources.
        return new EmprendedorCollection($emprendedores);
    }

    /**
     * Almacena un nuevo emprendedor en la base de datos.
     * Usado por: el formulario público de registro.
     * Ruta: POST /api/emprendedores
     */
    public function store(Request $request)
    {
        // Validación completa que coincide con tu formulario y base de datos
        $validator = Validator::make($request->all(), [
            // Paso 1: Información General
            'nombre' => 'required|string|max:20',
            'apellido1' => 'required|string|max:10',
            'apellido2' => 'required|string|max:10',
            'cedula' => 'required|string|max:12|unique:emprendedor,cedula',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|string|max:8',
            'correo_electronico' => 'nullable|email|max:50',
            'direccion' => 'required|string',
            'distrito' => 'required|string',
            'comunidad' => 'required|string|max:15',
            'escolaridad' => 'required|array',
            'certificaciones' => 'nullable|string',

            // Paso 2: Información del Emprendimiento (opcional)
            'nombre_emprendimiento' => 'sometimes|required|string|max:50',
            'tipo_emprendimiento' => 'sometimes|required|string|max:50',
            'descripcion_emprendimiento' => 'sometimes|required|string',
            'slogan' => 'nullable|string',
            'anio_inicio' => 'nullable|integer|min:1900',
            'numero_empleados' => 'nullable|integer|min:0',
            'mobiliario' => 'nullable|string',
            'signos_externos' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Usamos una transacción para asegurar que todo se guarde o no se guarde nada.
        $emprendedor = null;
        DB::transaction(function () use ($request, &$emprendedor) {
            // 1. Crear el Emprendedor
            $emprendedor = Emprendedor::create($request->only([
                'nombre', 'apellido1', 'apellido2', 'cedula', 'fecha_nacimiento',
                'telefono', 'correo_electronico', 'direccion', 'distrito', 'comunidad',
                'escolaridad', 'certificaciones'
            ]));

            // 2. Crear el Emprendimiento si se enviaron datos para él
            if ($request->filled('nombre_emprendimiento')) {
                $emprendimiento = $emprendedor->emprendimientos()->create($request->only([
                    'nombre_emprendimiento', 'tipo_emprendimiento', 'descripcion_emprendimiento',
                    'slogan', 'anio_inicio', 'numero_empleados', 'mobiliario', 'signos_externos'
                ]));

                // 3. Crear Proyección
                if ($request->hasAny(['intereses', 'ingreso_mensual'])) {
                    $emprendimiento->proyeccion()->create($request->only(['intereses', 'ingreso_mensual']));
                }

                // 4. Sincronizar relaciones (tablas pivote)
                if ($request->filled('formalizaciones')) {
                    $formalizacionIds = Formalizacion::whereIn('tipo_formalizacion', $request->formalizaciones)->pluck('id_formalizacion');
                    $emprendimiento->formalizaciones()->sync($formalizacionIds);
                }
                if ($request->filled('apoyos')) {
                    $apoyoIds = Apoyo::whereIn('tipo_apoyo', $request->apoyos)->pluck('id_apoyo');
                    $emprendimiento->apoyos()->sync($apoyoIds);
                }
                if ($request->filled('necesidades') && !empty($request->necesidades[0])) {
                    // Crea la necesidad si no existe, y obtiene su ID
                    $necesidad = Necesidad::firstOrCreate(['descripcion_necesidad' => $request->necesidades[0]]);
                    $emprendimiento->necesidades()->sync([$necesidad->id_necesidad]);
                }
                if ($request->filled('sector')) {
                    // Crea el sector si no existe, y obtiene su ID
                    $sector = Sector::firstOrCreate(['nombre_sector' => $request->sector]);
                    $emprendimiento->sectores()->sync([$sector->id_sector]);
                }
            }
        });

        return response()->json(new EmprendedorResource($emprendedor->load('emprendimientos')), 201);
    }

    /**
     * Muestra un emprendedor específico con todos sus detalles.
     * Usado por: el modal de "Ver Detalles" en tu dashboard.
     * Ruta: GET /api/emprendedores/{emprendedor}
     */
    public function show(Emprendedor $emprendedor)
    {
        // ¡CLAVE! Esta función carga TODAS las relaciones necesarias para la vista de detalles.
        return new EmprendedorResource($emprendedor->load([
            'emprendimientos.apoyos',
            'emprendimientos.ferias',
            'emprendimientos.formalizaciones',
            'emprendimientos.necesidades',
            'emprendimientos.redesSociales',
            'emprendimientos.sectores',
            'emprendimientos.proyeccion'
        ]));
    }

    /**
     * Actualiza un emprendedor existente.
     * Usado por: un futuro formulario de "Editar".
     * Ruta: PUT /api/emprendedores/{emprendedor}
     */
    public function update(Request $request, Emprendedor $emprendedor)
    {
        $validator = Validator::make($request->all(), [
            'cedula' => [
                'required',
                'string',
                'max:12',
                Rule::unique('emprendedor')->ignore($emprendedor->id_emprendedor, 'id_emprendedor')
            ],
            // Añade aquí el resto de validaciones para la actualización...
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $emprendedor->update($request->all());

        return new EmprendedorResource($emprendedor->load('emprendimientos'));
    }

    /**
     * Elimina un emprendedor de la base de datos.
     * Usado por: el botón "Eliminar" en tu dashboard.
     * Ruta: DELETE /api/emprendedores/{emprendedor}
     */
    public function destroy(Emprendedor $emprendedor)
    {
        DB::transaction(function () use ($emprendedor) {
            foreach ($emprendedor->emprendimientos as $emprendimiento) {
                // Desasocia todas las relaciones M:N para evitar errores de restricción
                $emprendimiento->apoyos()->detach();
                $emprendimiento->ferias()->detach();
                $emprendimiento->formalizaciones()->detach();
                $emprendimiento->necesidades()->detach();
                $emprendimiento->redesSociales()->detach();
                $emprendimiento->sectores()->detach();

                // Elimina registros relacionados 1:1 como Proyeccion
                if ($emprendimiento->proyeccion) {
                    $emprendimiento->proyeccion->delete();
                }
            }

            // Elimina los emprendimientos asociados
            $emprendedor->emprendimientos()->delete();

            // Finalmente, elimina al emprendedor
            $emprendedor->delete();
        });

        // La respuesta estándar para una eliminación exitosa es 204 (No Content).
        return response()->json(null, 204);
    }
}