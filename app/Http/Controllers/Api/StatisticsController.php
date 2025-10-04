use App\Http\Controllers\Controller;
use App\Models\Emprendedor;
use App\Models\Emprendimiento;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        // 1. KPIs Principales (Tarjetas)
        $totalEmprendedores = Emprendedor::count();
        $totalEmprendimientos = Emprendimiento::count();
        
        // 2. Gráfico: Emprendimientos por Sector
        $emprendimientosPorSector = DB::table('emprendimiento_sector')
            ->join('sector', 'emprendimiento_sector.id_sector', '=', 'sector.id_sector')
            ->select('sector.nombre_sector', DB::raw('count(*) as total'))
            ->groupBy('sector.nombre_sector')
            ->orderBy('total', 'desc')
            ->get();
            
        // 3. Gráfico: Emprendimientos por Año de Inicio
        $emprendimientosPorAnio = Emprendimiento::select('anio_inicio', DB::raw('count(*) as total'))
            ->groupBy('anio_inicio')
            ->orderBy('anio_inicio', 'asc')
            ->get();

        // 4. Gráfico: Emprendimientos por Distrito (Top 5)
        $emprendimientosPorDistrito = Emprendedor::select('distrito', DB::raw('count(*) as total'))
            ->groupBy('distrito')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // 5. Devolver todo en una sola respuesta JSON
        return response()->json([
            'data' => [
                'kpis' => [
                    'total_emprendedores' => $totalEmprendedores,
                    'total_emprendimientos' => $totalEmprendimientos,
                ],
                'emprendimientos_por_sector' => $emprendimientosPorSector,
                'emprendimientos_por_anio' => $emprendimientosPorAnio,
                'emprendimientos_por_distrito' => $emprendimientosPorDistrito,
            ]
        ]);
    }
}
