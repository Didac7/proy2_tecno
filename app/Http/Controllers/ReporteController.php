<?php

namespace App\Http\Controllers;

use App\Models\Paquete;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\Ruta;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function index()
    {
        // Estadísticas generales
        $stats = [
            // Ingresos
            'ingresos_hoy' => Pago::whereDate('fecha_pago', today())->sum('monto_total') ?? 0,
            'ingresos_mes' => Pago::whereMonth('fecha_pago', now()->month)->sum('monto_total') ?? 0,
            'ingresos_pendientes' => Pago::where('estado_pago', 'PENDIENTE')->sum('monto_total') ?? 0,
            
            // Paquetes
            'paquetes_hoy' => Paquete::whereDate('fecha_registro', today())->count(),
            'paquetes_mes' => Paquete::whereMonth('fecha_registro', now()->month)->count(),
            'paquetes_en_transito' => Paquete::where('estado_paquete', 'EN_TRANSITO')->count(),
            'paquetes_entregados_mes' => Paquete::where('estado_paquete', 'ENTREGADO')
                ->whereMonth('fecha_entrega_real', now()->month)->count(),
            
            // Clientes
            'clientes_nuevos_hoy' => Usuario::where('tipo_usuario', 'CLIENTE')
                ->whereDate('fecha_registro', today())->count(),
            'total_clientes' => Usuario::where('tipo_usuario', 'CLIENTE')->count(),
            
            // Vehículos
            'vehiculos_activos' => Vehiculo::where('estado', 'EN_RUTA')->count(),
            'vehiculos_disponibles' => Vehiculo::where('estado', 'DISPONIBLE')->count(),
        ];

        // Paquetes por estado (para gráfico)
        $paquetesPorEstado = Paquete::select('estado_paquete', DB::raw('count(*) as total'))
            ->groupBy('estado_paquete')
            ->get();

        // Ingresos últimos 7 días (para gráfico)
        $ingresos7Dias = Pago::select(
                DB::raw('DATE(fecha_pago) as fecha'),
                DB::raw('SUM(monto_total) as total')
            )
            ->where('fecha_pago', '>=', now()->subDays(7))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Top 5 rutas más usadas
        $topRutas = Ruta::withCount('paquetes')
            ->with(['origen', 'destino'])
            ->orderBy('paquetes_count', 'desc')
            ->limit(5)
            ->get();

        // Top 5 clientes
        $topClientes = Usuario::where('tipo_usuario', 'CLIENTE')
            ->withCount(['paquetesComoDestinatario as paquetes_count'])
            ->orderBy('paquetes_count', 'desc')
            ->limit(5)
            ->get();

        // Rendimiento de vehículos
        $vehiculosRendimiento = Vehiculo::with('chofer')
            ->withCount('paquetes')
            ->orderBy('paquetes_count', 'desc')
            ->get();

        return Inertia::render('Reportes/Index', [
            'stats' => $stats,
            'paquetesPorEstado' => $paquetesPorEstado,
            'ingresos7Dias' => $ingresos7Dias,
            'topRutas' => $topRutas,
            'topClientes' => $topClientes,
            'vehiculosRendimiento' => $vehiculosRendimiento,
        ]);
    }

    public function diario()
    {
        $reporte = Paquete::whereDate('fecha_registro', today())
            ->with(['destinatario', 'ruta'])
            ->get();
            
        return Inertia::render('Reportes/Index', [
            'activeTab' => 'diario',
            'data' => $reporte
        ]);
    }

    public function clientes()
    {
        $reporte = Usuario::where('tipo_usuario', 'CLIENTE')
            ->withCount('paquetesComoDestinatario as paquetes_count')
            ->orderBy('paquetes_count', 'desc')
            ->take(20)
            ->get();

        return Inertia::render('Reportes/Index', [
            'activeTab' => 'clientes',
            'data' => $reporte
        ]);
    }

    public function vehiculos()
    {
        $reporte = Vehiculo::withCount(['paquetes' => function($q) {
            $q->where('estado_paquete', 'EN_TRANSITO');
        }])->get();

        return Inertia::render('Reportes/Index', [
            'activeTab' => 'vehiculos',
            'data' => $reporte
        ]);
    }

    public function rutas()
    {
        $reporte = Ruta::withCount('paquetes')
            ->with(['origen', 'destino'])
            ->orderBy('paquetes_count', 'desc')
            ->get();

        return Inertia::render('Reportes/Index', [
            'activeTab' => 'rutas',
            'data' => $reporte
        ]);
    }

    public function exportarPdf()
    {
        // Recopilar mismos datos que el index
        $stats = [
            'ingresos_hoy' => Pago::whereDate('fecha_pago', today())->sum('monto_total') ?? 0,
            'paquetes_hoy' => Paquete::whereDate('fecha_registro', today())->count(),
            'clientes_nuevos_hoy' => Usuario::where('tipo_usuario', 'CLIENTE')
                ->whereDate('fecha_registro', today())->count(),
        ];

        $paquetesPorEstado = Paquete::select('estado_paquete', DB::raw('count(*) as total'))
            ->groupBy('estado_paquete')
            ->get();

        $topRutas = Ruta::withCount('paquetes')
            ->with(['origen', 'destino'])
            ->orderBy('paquetes_count', 'desc')
            ->limit(5)
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.pdf', compact('stats', 'paquetesPorEstado', 'topRutas'));
        
        return $pdf->download('reporte-transvelasco-' . now()->format('Y-m-d') . '.pdf');
    }
}
