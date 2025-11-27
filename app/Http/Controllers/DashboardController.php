<?php

namespace App\Http\Controllers;

use App\Models\Paquete;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Los clientes no ven el dashboard, van directo a sus paquetes
        if ($user->tipo_usuario === 'CLIENTE') {
            return redirect()->route('paquetes.index');
        }

        // Dashboard especial para CHOFERES
        if ($user->tipo_usuario === 'CHOFER') {
            // Buscar vehículo asignado
            $vehiculo = Vehiculo::where('id_chofer', $user->id_usuario)->first();

            // Paquetes asignados a este chofer (a través de su vehículo)
            $paquetes_asignados = collect([]);
            if ($vehiculo) {
                $paquetes_asignados = Paquete::where('id_vehiculo', $vehiculo->id_vehiculo)
                    ->with(['ruta.origen', 'ruta.destino', 'remitente'])
                    ->orderBy('fecha_registro', 'desc')
                    ->get();
            }

            return Inertia::render('Dashboard', [
                'stats' => [
                    'total_paquetes' => $paquetes_asignados->count(),
                    'paquetes_en_transito' => $paquetes_asignados->where('estado_paquete', 'EN_TRANSITO')->count(),
                    'paquetes_entregados' => $paquetes_asignados->where('estado_paquete', 'ENTREGADO')->count(),
                    'total_clientes' => 0,
                    'vehiculos_activos' => 0,
                    'pagos_pendientes' => 0,
                ],
                'paquetes_recientes' => $paquetes_asignados,
                'paquetes_por_estado' => [],
                'es_chofer' => true,
            ]);
        }

        // Dashboard para ADMIN/SECRETARIA/PROPIETARIO
        $stats = [
            'total_paquetes' => Paquete::count(),
            'paquetes_en_transito' => Paquete::where('estado_paquete', 'EN_TRANSITO')->count(),
            'paquetes_entregados' => Paquete::where('estado_paquete', 'ENTREGADO')->count(),
            'total_clientes' => Usuario::where('tipo_usuario', 'CLIENTE')->count(),
            'vehiculos_activos' => Vehiculo::where('estado', 'DISPONIBLE')->count(),
            'pagos_pendientes' => Pago::where('estado_pago', 'PENDIENTE')->sum('monto_total'),
        ];

        $paquetes_recientes = Paquete::with(['destinatario', 'remitente', 'ruta.origen', 'ruta.destino', 'vehiculo'])
            ->orderBy('fecha_registro', 'desc')
            ->limit(10)
            ->get();

        // Estadísticas por estado (para gráficos)
        $paquetes_por_estado = Paquete::select('estado_paquete', DB::raw('count(*) as total'))
            ->groupBy('estado_paquete')
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'paquetes_recientes' => $paquetes_recientes,
            'paquetes_por_estado' => $paquetes_por_estado,
            'es_chofer' => false,
        ]);
    }
}
