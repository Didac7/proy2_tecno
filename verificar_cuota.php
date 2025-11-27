<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Buscar el paquete AWE-2025-003
$paquete = App\Models\Paquete::where('codigo_seguimiento', 'AWE-2025-003')->first();

if (!$paquete) {
    echo "Paquete no encontrado\n";
    exit;
}

$pago = $paquete->pagos()->first();
$plan = $pago->planPago;
$cuotas = $plan->cuotas;

echo "=== VERIFICACIÓN DE CUOTAS ===\n";
echo "Paquete: {$paquete->codigo_seguimiento}\n";
echo "Plan ID: {$plan->id_plan}\n";
echo "Cuotas Pagadas (contador): {$plan->cuotas_pagadas}\n";
echo "Total Cuotas: {$plan->numero_cuotas}\n\n";

echo "=== DETALLE DE CUOTAS ===\n";
foreach ($cuotas as $cuota) {
    echo "Cuota {$cuota->numero_cuota}: {$cuota->estado}";
    if ($cuota->estado === 'PAGADA') {
        echo " (Pagada el {$cuota->fecha_pago})";
    }
    echo "\n";
}

// Contar cuotas pagadas realmente
$cuotasPagadasReal = $cuotas->where('estado', 'PAGADA')->count();
echo "\nCuotas realmente pagadas: {$cuotasPagadasReal}\n";

if ($cuotasPagadasReal != $plan->cuotas_pagadas) {
    echo "\n⚠️ INCONSISTENCIA DETECTADA!\n";
    echo "El contador dice {$plan->cuotas_pagadas} pero hay {$cuotasPagadasReal} cuotas pagadas\n";
    echo "Corrigiendo...\n";
    
    $plan->cuotas_pagadas = $cuotasPagadasReal;
    $plan->save();
    
    echo "✅ Corregido!\n";
}
