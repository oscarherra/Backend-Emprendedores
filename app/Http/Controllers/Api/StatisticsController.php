<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Emprendedor;

class StatisticsController extends Controller
{
    public function index()
    {
        try {
            // --- CÁLCULOS PRINCIPALES ---

            // KPI 1: Total de emprendedores registrados (Este ya era correcto)
            $totalEmprendedores = Emprendedor::count();

            // KPI 2: Total de emprendimientos ACTIVOS
            // ¡CORRECCIÓN! Ahora solo contamos emprendimientos que tienen un emprendedor válido asociado.
            $totalEmprendimientos = DB::table('emprendimiento')
                ->join('emprendedor', 'emprendimiento.id_emprendedor', '=', 'emprendedor.id_emprendedor')
                ->count();

            // GRÁFICO 1: Emprendimientos por Sector
            // ¡CORRECCIÓN! También aplicamos el JOIN aquí para no contar registros fantasma.
            $emprendimientosPorSector = DB::table('sector')
                ->join('emprendimiento_sector', 'sector.id_sector', '=', 'emprendimiento_sector.id_sector')
                ->join('emprendimiento', 'emprendimiento_sector.id_emprendimiento', '=', 'emprendimiento.id_emprendimiento')
                ->join('emprendedor', 'emprendimiento.id_emprendedor', '=', 'emprendedor.id_emprendedor')
                ->select('sector.nombre_sector', DB::raw('COUNT(emprendimiento_sector.id_emprendimiento) as total'))
                ->groupBy('sector.nombre_sector')
                ->orderBy('total', 'desc')
                ->get();

            // --- NUEVOS GRÁFICOS ---

            // GRÁFICO 2: Emprendedores por Distrito (para un gráfico de pastel)
            $emprendedoresPorDistrito = DB::table('emprendedor')
                ->select('distrito', DB::raw('COUNT(id_emprendedor) as total'))
                ->groupBy('distrito')
                ->orderBy('total', 'desc')
                ->get();

            // GRÁFICO 3: Apoyos más solicitados (para un gráfico de barras horizontal)
            $apoyosSolicitados = DB::table('apoyo')
                ->join('emprendimiento_apoyo', 'apoyo.id_apoyo', '=', 'emprendimiento_apoyo.id_apoyo')
                ->select('apoyo.tipo_apoyo', DB::raw('COUNT(emprendimiento_apoyo.id_emprendimiento) as total'))
                ->groupBy('apoyo.tipo_apoyo')
                ->orderBy('total', 'desc')
                ->get();
            
            // --- RESPUESTA JSON COMPLETA ---
            return response()->json([
                'data' => [
                    'kpis' => [
                        'total_emprendedores' => $totalEmprendedores,
                        'total_emprendimientos' => $totalEmprendimientos,
                    ],
                    'emprendimientos_por_sector' => $emprendimientosPorSector,
                    'emprendedores_por_distrito' => $emprendedoresPorDistrito, // Nuevo dato
                    'apoyos_solicitados' => $apoyosSolicitados, // Nuevo dato
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al calcular las estadísticas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}