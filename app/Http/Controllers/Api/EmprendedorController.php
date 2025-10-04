<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Emprendedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmprendedorController extends Controller
{
    /**
     * Almacena un nuevo emprendedor con toda su información relacionada.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:20',
            'apellido1' => 'required|string|max:10',
            'apellido2' => 'required|string|max:10',
            'cedula' => 'required|string|max:12|unique:emprendedor,cedula',
            // ... Agrega aquí todas las validaciones para el emprendedor
            
            'nombre_emprendimiento' => 'required|string|max:50',
            // ... Agrega aquí todas las validaciones para el emprendimiento
            
            'apoyos' => 'sometimes|array', // Array de IDs de apoyos
            'apoyos.*' => 'integer|exists:apoyo,id_apoyo',
            'ferias' => 'sometimes|array', // Array de IDs de ferias
            // ... Agrega las validaciones para los demás arrays de IDs
            'redes_sociales' => 'sometimes|array', // Esperamos un array de objetos: [{id: 1, url: '...'}, ...]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // 2. Usar una transacción para asegurar que todo se guarde o nada se guarde
        DB::beginTransaction();
        try {
            // 3. Crear el Emprendedor
            $emprendedor = Emprendedor::create([
                'nombre' => $request->input('nombre'),
                'apellido1' => $request->input('apellido1'),
                'apellido2' => $request->input('apellido2'),
                'cedula' => $request->input('cedula'),
                'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                'telefono' => $request->input('telefono'),
                'correo_electronico' => $request->input('correo_electronico'),
                'direccion' => $request->input('direccion'),
                'distrito' => $request->input('distrito'),
                'comunidad' => $request->input('comunidad'),
                'escolaridad' => $request->input('escolaridad'), // Laravel lo codifica a JSON
                'certificaciones' => $request->input('certificaciones'),
            ]);

            // 4. Crear el Emprendimiento asociado al Emprendedor
            $emprendimiento = $emprendedor->emprendimientos()->create([
                'nombre_emprendimiento' => $request->input('nombre_emprendimiento'),
                'tipo_emprendimiento' => $request->input('tipo_emprendimiento'),
                'descripcion_emprendimiento' => $request->input('descripcion_emprendimiento'),
                'slogan' => $request->input('slogan'),
                'anio_inicio' => $request->input('anio_inicio'),
                'numero_empleados' => $request->input('numero_empleados'),
                'mobiliario' => $request->input('mobiliario'),
                'signos_externos' => $request->input('signos_externos'),
            ]);
            
            // 5. Sincronizar las relaciones "Muchos a Muchos"
            if ($request->has('apoyos')) {
                $emprendimiento->apoyos()->sync($request->input('apoyos'));
            }
            if ($request->has('ferias')) {
                $emprendimiento->ferias()->sync($request->input('ferias'));
            }
            if ($request->has('formalizaciones')) {
                $emprendimiento->formalizaciones()->sync($request->input('formalizaciones'));
            }
             if ($request->has('necesidades')) {
                $emprendimiento->necesidades()->sync($request->input('necesidades'));
            }
            
            // Para la relación con datos extra (redes sociales)
            if ($request->has('redes_sociales')) {
                $redesSyncData = [];
                foreach ($request->input('redes_sociales') as $red) {
                    $redesSyncData[$red['id_red_social']] = ['url_usuario' => $red['url_usuario']];
                }
                $emprendimiento->redesSociales()->sync($redesSyncData);
            }
            
            // 6. Confirmar la transacción
            DB::commit();
            
            return response()->json([
                'message' => 'Emprendedor y emprendimiento creados con éxito!',
                'data' => $emprendedor->load('emprendimientos') // Devuelve el emprendedor con su info
            ], 201);

        } catch (\Exception $e) {
            // 7. Si algo falla, revertir todo
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el registro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}