<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PagoFacilService;

class TestPagoFacil extends Command
{
    protected $signature = 'test:pagofacil';
    protected $description = 'Probar conexión con PagoFácil';

    public function handle()
    {
        $this->info('=== TEST PAGOFACIL ===');
        
        $service = app(PagoFacilService::class);
        
        // Test 1: Login
        $this->info("\n1. Probando login...");
        try {
            $token = $service->login();
            $this->info("✓ Login exitoso");
            $this->line("Token: " . substr($token, 0, 30) . "...");
        } catch (\Exception $e) {
            $this->error("✗ Error de login: " . $e->getMessage());
            return 1;
        }
        
        // Test 2: List Services
        $this->info("\n2. Probando listar servicios...");
        try {
            $services = $service->listEnabledServices();
            $this->info("✓ Servicios obtenidos");
            $this->line(json_encode($services, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error("✗ Error al listar servicios: " . $e->getMessage());
        }
        
        // Test 3: Generate QR
        $this->info("\n3. Probando generar QR...");
        try {
            $paymentData = [
                'client_name' => 'Test User',
                'document_id' => '12345678',
                'phone_number' => '72971922',
                'email' => 'test@test.com',
                'payment_number' => 'TEST-' . time(),
                'amount' => 1.00,
                'client_code' => '1',
                'order_detail' => [
                    [
                        'serial' => 1,
                        'product' => 'Test Product',
                        'quantity' => 1,
                        'price' => 1.00,
                        'discount' => 0,
                        'total' => 1.00,
                    ]
                ],
            ];
            
            $qr = $service->generateQR($paymentData);
            $this->info("✓ QR generado exitosamente");
            $this->line("Transaction ID: " . ($qr['transactionId'] ?? 'N/A'));
        } catch (\Exception $e) {
            $this->error("✗ Error al generar QR: " . $e->getMessage());
            return 1;
        }
        
        $this->info("\n✓ Todas las pruebas pasaron");
        return 0;
    }
}
