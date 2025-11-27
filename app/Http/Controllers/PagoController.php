<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Paquete;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PagoController extends Controller
{
    protected $pagoFacilService;

    public function __construct(PagoFacilService $pagoFacilService)
    {
        $this->pagoFacilService = $pagoFacilService;
    }

    /**
     * Listar pagos
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Solo mostrar pagos que tengan un paquete válido
        $query = Pago::with(['paquete.remitente', 'paquete.destinatario'])
                     ->whereHas('paquete');
        
        // Filtrar por cliente si es un cliente
        if ($user->isCliente()) {
            // Mostrar pagos donde el usuario es remitente o destinatario del paquete
            $query->whereHas('paquete', function($q) use ($user) {
                $q->where('id_remitente', $user->id_usuario)
                  ->orWhere('id_destinatario', $user->id_usuario);
            });
        }
        
        // Búsqueda general (código de transacción, código de paquete, nombre de cliente)
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_transaccion', 'ilike', '%' . $search . '%')
                  ->orWhereHas('paquete', function($subQ) use ($search) {
                      $subQ->where('codigo_seguimiento', 'ilike', '%' . $search . '%')
                           ->orWhereHas('remitente', function($userQ) use ($search) {
                               $userQ->where('nombre', 'ilike', '%' . $search . '%')
                                     ->orWhere('apellido', 'ilike', '%' . $search . '%')
                                     ->orWhere('ci', 'ilike', '%' . $search . '%');
                           })
                           ->orWhereHas('destinatario', function($userQ) use ($search) {
                               $userQ->where('nombre', 'ilike', '%' . $search . '%')
                                     ->orWhere('apellido', 'ilike', '%' . $search . '%')
                                     ->orWhere('ci', 'ilike', '%' . $search . '%');
                           });
                  });
            });
        }
        
        // Filtro por estado
        if ($request->estado) {
            $query->where('estado_pago', $request->estado);
        }
        
        // Filtro por cliente específico
        if ($request->cliente) {
            $query->whereHas('paquete', function($q) use ($request) {
                $q->where('id_remitente', $request->cliente)
                  ->orWhere('id_destinatario', $request->cliente);
            });
        }
        
        $pagos = $query->orderBy('fecha_pago', 'desc')
                       ->orderBy('id_pago', 'desc')
                       ->paginate(15);
        
        // Obtener lista de clientes para el filtro (solo para admin/secretaria)
        $clientes = [];
        if (!$user->isCliente()) {
            $clientes = \App\Models\Usuario::where('tipo_usuario', 'CLIENTE')
                ->where('estado', 'ACTIVO')
                ->select('id_usuario', 'nombre', 'apellido', 'ci')
                ->orderBy('nombre')
                ->get();
        }
        
        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'clientes' => $clientes,
            'filters' => $request->only(['search', 'estado', 'cliente']),
        ]);
    }

    /**
     * Mostrar formulario de pago para un paquete
     */
    public function create($id_paquete)
    {
        $paquete = Paquete::with(['remitente', 'destinatario', 'ruta.origen', 'ruta.destino'])
            ->findOrFail($id_paquete);

        // Verificar si ya existe un pago
        $pagoExistente = Pago::where('id_paquete', $id_paquete)->first();

        if ($pagoExistente && $pagoExistente->estado_pago === 'PAGADO') {
            return redirect()->route('paquetes.show', $id_paquete)
                ->with('error', 'Este paquete ya ha sido pagado.');
        }

        // Si ya existe un plan de cuotas, redirigir directamente a la página de cuotas
        if ($pagoExistente && $pagoExistente->planPago) {
            return redirect()->route('pagos.cuotas', $pagoExistente->id_pago)
                ->with('info', 'Este paquete ya tiene un plan de cuotas activo.');
        }

        // Calcular monto
        $monto = $this->calcularMontoPaquete($paquete);

        // Opciones de cuotas disponibles
        $opcionesCuotas = [
            ['value' => 3, 'label' => '3 cuotas'],
            ['value' => 6, 'label' => '6 cuotas'],
            ['value' => 12, 'label' => '12 cuotas'],
        ];

        return Inertia::render('Pagos/Create', [
            'paquete' => $paquete,
            'monto' => floatval($monto),
            'pagoExistente' => $pagoExistente,
            'opcionesCuotas' => $opcionesCuotas,
        ]);
    }

    /**
     * Crear pago (contado o crédito)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_paquete' => 'required|exists:paquetes,id_paquete',
            'tipo_pago' => 'required|in:CONTADO,CREDITO',
            'numero_cuotas' => 'required_if:tipo_pago,CREDITO|integer|in:3,6,12',
            'metodo_cobro' => 'nullable|in:QR,EFECTIVO',
            'observaciones' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $paquete = Paquete::findOrFail($validated['id_paquete']);
            $monto = $this->calcularMontoPaquete($paquete);

            // Determinar estado inicial según método de cobro
            $estadoPago = 'PENDIENTE';
            $fechaPago = null;
            $montoPagado = 0;
            $metodoPago = $validated['metodo_cobro'] ?? ($validated['tipo_pago'] === 'CONTADO' ? 'QR' : null);

            // Si es cobro en EFECTIVO
            if ($metodoPago === 'EFECTIVO') {
                if ($validated['tipo_pago'] === 'CONTADO') {
                    // Contado + Efectivo = Pagado Totalmente
                    $estadoPago = 'PAGADO';
                    $fechaPago = now();
                    $montoPagado = $monto;
                } else {
                    // Crédito + Efectivo = Pendiente (se pagan cuotas después)
                    // A menos que se quiera implementar un pago inicial, por ahora lo dejamos pendiente
                    // y el usuario debe ir a pagar la primera cuota.
                    $estadoPago = 'PENDIENTE';
                    $montoPagado = 0;
                }
            }

            // Crear o actualizar pago
            $pago = Pago::updateOrCreate(
                ['id_paquete' => $validated['id_paquete']],
                [
                    'monto_total' => $monto,
                    'monto_pagado' => $montoPagado,
                    'tipo_pago' => $validated['tipo_pago'],
                    'estado_pago' => $estadoPago,
                    'fecha_pago' => $fechaPago,
                    'metodo_pago' => $metodoPago,
                    'numero_transaccion' => 'PAY-' . $validated['id_paquete'] . '-' . time(),
                    // Podríamos guardar observaciones en algún campo si existiera en la BD
                ]
            );

            // Si es a crédito, crear plan de cuotas
            if ($validated['tipo_pago'] === 'CREDITO') {
                $numeroCuotas = $validated['numero_cuotas'];
                $montoCuota = round($monto / $numeroCuotas, 2);

                // Crear plan de pago
                $plan = \App\Models\PlanPago::updateOrCreate(
                    ['id_pago' => $pago->id_pago],
                    [
                        'numero_cuotas' => $numeroCuotas,
                        'monto_cuota' => $montoCuota,
                        'cuotas_pagadas' => 0,
                    ]
                );

                // Eliminar cuotas existentes si las hay
                \App\Models\Cuota::where('id_plan', $plan->id_plan)->delete();

                // Crear cuotas
                for ($i = 1; $i <= $numeroCuotas; $i++) {
                    \App\Models\Cuota::create([
                        'id_plan' => $plan->id_plan,
                        'numero_cuota' => $i,
                        'monto' => $montoCuota,
                        'fecha_vencimiento' => now()->addMonths($i)->format('Y-m-d'),
                        'estado' => 'PENDIENTE',
                    ]);
                }

                DB::commit();

                return redirect()->route('pagos.cuotas', $pago->id_pago)
                    ->with('success', 'Plan de cuotas creado exitosamente.');
            }

            // Si es pago en EFECTIVO y CONTADO, actualizar estado del paquete
            if ($metodoPago === 'EFECTIVO' && $validated['tipo_pago'] === 'CONTADO') {
                if ($paquete->estado_paquete === 'REGISTRADO') {
                    $paquete->update([
                        'estado_paquete' => 'EN_TRANSITO',
                    ]);
                }
                
                DB::commit();
                
                return redirect()->route('paquetes.index')
                    ->with('success', 'Cobro en efectivo registrado correctamente.');
            }

            DB::commit();

            // Si es QR, redirigir a generar QR
            if ($metodoPago === 'QR') {
                return redirect()->route('pagos.create', $validated['id_paquete'])
                    ->with('success', 'Pago creado. Ahora genera el código QR.')
                    ->with('mostrarQR', true);
            }

            return redirect()->route('pagos.create', $validated['id_paquete'])
                ->with('success', 'Pago creado.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear pago', ['error' => $e->getMessage()]);
            
            return back()->with('error', 'Error al crear el pago: ' . $e->getMessage());
        }
    }

    /**
     * Generar QR de pago
     */
    public function generarQR(Request $request, $id_paquete)
    {
        try {
            $paquete = Paquete::with(['destinatario', 'remitente'])->findOrFail($id_paquete);

            // Verificar si ya existe un pago pendiente
            $pago = Pago::where('id_paquete', $id_paquete)->first();

            if (!$pago) {
                // Crear registro de pago
                $monto = $this->calcularMontoPaquete($paquete);

                $pago = Pago::create([
                    'id_paquete' => $id_paquete,
                    'monto_total' => $monto,
                    'metodo_pago' => 'QR',
                    'estado_pago' => 'PENDIENTE',
                    'numero_transaccion' => 'PAY-' . $id_paquete . '-' . uniqid(),
                ]);
            } else {
                // Si ya existe, actualizar el número de transacción para evitar duplicados en PagoFácil
                // y asegurar que el método de pago sea QR
                $pago->update([
                    'numero_transaccion' => 'PAY-' . $id_paquete . '-' . uniqid(),
                    'metodo_pago' => 'QR'
                ]);
            }

            // Preparar datos para PagoFácil
            // Usar datos del remitente (quien paga) en lugar del destinatario
            $cliente = $paquete->remitente;
            
            // IMPORTANTE: PagoFácil requiere mínimo 1 Bs
            $montoParaQR = max(1.00, floatval($pago->monto_total));

            $paymentData = [
                'client_name' => $cliente->nombre . ' ' . $cliente->apellido,
                'document_id' => $cliente->ci ?? '0000000',
                'phone_number' => $cliente->telefono ?? '72971922',
                'email' => $cliente->email ?? 'cliente@transvelasco.com',
                'payment_number' => $pago->numero_transaccion,
                'amount' => $montoParaQR,
                'client_code' => strval($cliente->id_usuario),
                'order_detail' => [
                    [
                        'serial' => 1,
                        'product' => 'Envío de paquete - ' . $paquete->codigo_seguimiento,
                        'quantity' => 1,
                        'price' => $montoParaQR,
                        'discount' => 0,
                        'total' => $montoParaQR,
                    ]
                ],
            ];

            // Generar QR
            $qrData = $this->pagoFacilService->generateQR($paymentData);

            // Actualizar pago con ID de transacción de PagoFácil
            $pago->update([
                'id_transaccion_pagofacil' => $qrData['transactionId'],
            ]);

            return response()->json([
                'success' => true,
                'qr' => $qrData,
                'pago' => $pago,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar QR', [
                'error' => $e->getMessage(),
                'paquete_id' => $id_paquete
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el código QR: ' . $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Consultar estado de un pago
     */
    public function consultarEstado($id_pago)
    {
        try {
            $pago = Pago::findOrFail($id_pago);

            if ($pago->id_transaccion_pagofacil) {
                // Consultar en PagoFácil
                $transactionData = $this->pagoFacilService->queryTransaction(
                    $pago->id_transaccion_pagofacil,
                    $pago->numero_transaccion
                );

                // Actualizar estado local
                $estadoPago = $this->mapearEstadoPago($transactionData['paymentStatus']);
                $pago->update([
                    'estado_pago' => $estadoPago,
                    'fecha_pago' => $transactionData['paymentDate'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'pago' => $pago,
                    'transaction_data' => $transactionData,
                ]);
            }

            return response()->json([
                'success' => true,
                'pago' => $pago,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el estado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calcular monto del paquete
     */
    private function calcularMontoPaquete($paquete)
    {
        // Retornar el precio ingresado al registrar el paquete
        return $paquete->precio ?? 1.00;
    }

    /**
     * Ver cuotas de un plan de pago
     */
    public function verCuotas($id_pago)
    {
        $pago = Pago::with(['paquete', 'planPago.cuotas'])->findOrFail($id_pago);

        if (!$pago->planPago) {
            return redirect()->route('pagos.index')
                ->with('error', 'Este pago no tiene un plan de cuotas.');
        }

        // Refrescar datos desde la base de datos para asegurar que estén actualizados
        $pago->planPago->refresh();
        $cuotas = $pago->planPago->cuotas()->orderBy('numero_cuota')->get();

        return Inertia::render('Pagos/Cuotas', [
            'pago' => $pago,
            'plan' => $pago->planPago,
            'cuotas' => $cuotas,
        ]);
    }

    /**
     * Pagar una cuota individual en EFECTIVO (Solo admin/secretaria)
     */
    public function pagarCuotaEfectivo(Request $request, $id_cuota)
    {
        try {
            Log::info('Iniciando pago de cuota en efectivo', ['cuota_id' => $id_cuota]);
            
            // Verificar permisos (aunque ya debería estar protegido por middleware/política)
            if (auth()->user()->isCliente()) {
                Log::warning('Cliente intentó pagar cuota en efectivo');
                return back()->with('error', 'No tiene permisos para realizar esta acción.');
            }

            $cuota = \App\Models\Cuota::with(['planPago.pago.paquete'])->findOrFail($id_cuota);
            Log::info('Cuota encontrada', ['estado' => $cuota->estado]);

            if ($cuota->estado === 'PAGADA') {
                Log::warning('Cuota ya estaba pagada');
                return back()->with('error', 'Esta cuota ya fue pagada.');
            }

            DB::beginTransaction();
            Log::info('Transacción iniciada');

            // Actualizar cuota
            $cuota->update([
                'estado' => 'PAGADA',
                'fecha_pago' => now(),
            ]);
            Log::info('Cuota actualizada', ['nuevo_estado' => $cuota->estado]);

            // Actualizar plan
            $plan = $cuota->planPago;
            $cuotasPagadasAntes = $plan->cuotas_pagadas;
            $plan->increment('cuotas_pagadas');
            $plan->refresh();
            Log::info('Plan actualizado', [
                'cuotas_pagadas_antes' => $cuotasPagadasAntes,
                'cuotas_pagadas_despues' => $plan->cuotas_pagadas
            ]);

            // Verificar si se completó el plan
            if ($plan->cuotas_pagadas >= $plan->numero_cuotas) {
                $pago = $plan->pago;
                $pago->update([
                    'estado_pago' => 'PAGADO',
                    'monto_pagado' => $pago->monto_total,
                    'fecha_pago' => now(), // Fecha del último pago
                ]);
                Log::info('Plan completado, pago actualizado');
            }

            DB::commit();
            Log::info('Transacción completada exitosamente');

            return back()->with('success', 'Pago de cuota registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar pago de cuota en efectivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cuota_id' => $id_cuota
            ]);

            return back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Pagar una cuota individual con QR
     */
    public function pagarCuota(Request $request, $id_cuota)
    {
        try {
            $cuota = \App\Models\Cuota::with(['planPago.pago.paquete'])->findOrFail($id_cuota);

            if ($cuota->estado === 'PAGADA') {
                return back()->with('error', 'Esta cuota ya fue pagada.');
            }

            // Preparar datos para PagoFácil
            $pago = $cuota->planPago->pago;
            $paquete = $pago->paquete;
            $cliente = $paquete->remitente;

            // IMPORTANTE: PagoFácil requiere mínimo 1 Bs
            $montoParaQR = max(1.00, floatval($cuota->monto));

            $paymentData = [
                'client_name' => $cliente->nombre . ' ' . $cliente->apellido,
                'document_id' => $cliente->ci ?? '0000000',
                'phone_number' => $cliente->telefono ?? '72971922',
                'email' => $cliente->email ?? 'cliente@transvelasco.com',
                'payment_number' => 'CUOTA-' . $id_cuota . '-' . uniqid(),
                'amount' => $montoParaQR,
                'client_code' => strval($cliente->id_usuario),
                'order_detail' => [
                    [
                        'serial' => 1,
                        'product' => 'Cuota ' . $cuota->numero_cuota . '/' . $cuota->planPago->numero_cuotas . ' - Paquete ' . $paquete->codigo_seguimiento,
                        'quantity' => 1,
                        'price' => $montoParaQR,
                        'discount' => 0,
                        'total' => $montoParaQR,
                    ]
                ],
            ];

            // Generar QR
            $qrData = $this->pagoFacilService->generateQR($paymentData);

            // Actualizar cuota con número de transacción
            $cuota->update([
                'numero_transaccion' => $qrData['transactionId'],
            ]);

            return response()->json([
                'success' => true,
                'qr' => $qrData,
                'cuota' => $cuota,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar QR para cuota', [
                'error' => $e->getMessage(),
                'cuota_id' => $id_cuota
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el código QR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mapear estado de PagoFácil a estado local
     */
    private function mapearEstadoPago($paymentStatus)
    {
        $estados = [
            1 => 'PENDIENTE',
            2 => 'PAGADO',
            3 => 'CANCELADO',
            4 => 'RECHAZADO',
            5 => 'REVISION',
        ];

        return $estados[$paymentStatus] ?? 'PENDIENTE';
    }

    /**
     * Webhook para recibir notificaciones de PagoFácil
     * Este método se ejecuta cuando PagoFácil notifica que un pago se completó
     */
    public function webhook(Request $request)
    {
        try {
            // Log de la notificación recibida
            Log::info('PagoFacil Webhook Recibido', [
                'payload' => $request->all()
            ]);

            // Validar que venga el ID de transacción
            $transactionId = $request->input('TransactionId') ?? $request->input('transactionId');
            
            if (!$transactionId) {
                Log::error('Webhook sin TransactionId', ['payload' => $request->all()]);
                return response()->json(['error' => 'TransactionId requerido'], 400);
            }

            // Buscar el pago por el ID de transacción de PagoFácil
            $pago = Pago::where('id_transaccion_pagofacil', $transactionId)->first();

            if (!$pago) {
                Log::warning('Pago no encontrado para TransactionId', ['transactionId' => $transactionId]);
                return response()->json(['error' => 'Pago no encontrado'], 404);
            }

            // Obtener el estado del pago desde PagoFácil
            $paymentStatus = $request->input('Status') ?? $request->input('status');
            
            // Si el pago fue exitoso (Status = 2 en PagoFácil)
            if ($paymentStatus == 2) {
                DB::beginTransaction();

                // Actualizar el pago
                $pago->update([
                    'estado_pago' => 'PAGADO',
                    'fecha_pago' => now(),
                    'monto_pagado' => $pago->monto_total,
                ]);

                // Si es un pago a crédito, actualizar la cuota correspondiente
                if ($pago->tipo_pago === 'CREDITO') {
                    $plan = $pago->planPago;
                    
                    if ($plan) {
                        // Buscar la primera cuota pendiente
                        $cuota = \App\Models\Cuota::where('id_plan', $plan->id_plan)
                            ->where('estado', 'PENDIENTE')
                            ->orderBy('numero_cuota')
                            ->first();

                        if ($cuota) {
                            $cuota->update([
                                'estado' => 'PAGADA',
                                'fecha_pago' => now(),
                            ]);

                            // Actualizar contador de cuotas pagadas
                            $plan->increment('cuotas_pagadas');

                            // Si todas las cuotas están pagadas, marcar el pago como completado
                            if ($plan->cuotas_pagadas >= $plan->numero_cuotas) {
                                $pago->update([
                                    'estado_pago' => 'PAGADO',
                                    'monto_pagado' => $pago->monto_total,
                                ]);
                            }
                        }
                    }
                }

                DB::commit();

                Log::info('Pago actualizado exitosamente', [
                    'pago_id' => $pago->id_pago,
                    'transactionId' => $transactionId,
                    'estado' => 'PAGADO'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago procesado correctamente'
                ]);
            } else {
                // Otros estados (cancelado, rechazado, etc.)
                $estadoMapeado = $this->mapearEstadoPago($paymentStatus);
                
                $pago->update([
                    'estado_pago' => $estadoMapeado,
                ]);

                Log::info('Pago actualizado con estado diferente', [
                    'pago_id' => $pago->id_pago,
                    'transactionId' => $transactionId,
                    'estado' => $estadoMapeado
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado'
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error procesando webhook de PagoFácil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error procesando notificación'
            ], 500);
        }
    }
}
