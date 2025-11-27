<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PagoFacilService
{
    private $urlBase;
    private $tokenService;
    private $tokenSecret;
    private $callbackUrl;

    public function __construct()
    {
        $this->urlBase = config('pagofacil.url_base');
        $this->tokenService = config('pagofacil.token_service');
        $this->tokenSecret = config('pagofacil.token_secret');
        $this->callbackUrl = config('pagofacil.callback_url');
    }

    /**
     * Obtener token de autenticación
     */
    public function login()
    {
        // Verificar si hay un token en caché válido
        $cachedToken = Cache::get('pagofacil_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::withHeaders([
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret' => $this->tokenSecret,
            ])->post($this->urlBase . '/login');

            $data = $response->json();

            if ($data['error'] === 0 && isset($data['values']['accessToken'])) {
                $token = $data['values']['accessToken'];
                $expiresInMinutes = $data['values']['expiresInMinutes'] ?? 60;

                // Guardar en caché (restar 5 minutos para renovar antes de que expire)
                Cache::put('pagofacil_access_token', $token, now()->addMinutes($expiresInMinutes - 5));

                return $token;
            }

            Log::error('PagoFacil Login Error', ['response' => $data]);
            throw new \Exception($data['message'] ?? 'Error al autenticar con PagoFácil');

        } catch (\Exception $e) {
            Log::error('PagoFacil Login Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Listar métodos de pago habilitados
     */
    public function listEnabledServices()
    {
        try {
            $token = $this->login();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->post($this->urlBase . '/list-enabled-services');

            $data = $response->json();

            if ($data['error'] === 0) {
                return $data['values'];
            }

            throw new \Exception($data['message'] ?? 'Error al listar servicios');

        } catch (\Exception $e) {
            Log::error('PagoFacil List Services Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generar QR para pago
     * 
     * @param array $paymentData Datos del pago
     * @return array Respuesta con QR y detalles de transacción
     */
    public function generateQR($paymentData)
    {
        try {
            $token = $this->login();

            $body = [
                'paymentMethod' => config('pagofacil.payment_method_id'),
                'clientName' => $paymentData['client_name'],
                'documentType' => config('pagofacil.document_type'),
                'documentId' => $paymentData['document_id'],
                'phoneNumber' => $paymentData['phone_number'],
                'email' => $paymentData['email'],
                'paymentNumber' => $paymentData['payment_number'], // ID único de la transacción
                'amount' => $paymentData['amount'],
                'currency' => config('pagofacil.currency'),
                'clientCode' => $paymentData['client_code'],
                'callbackUrl' => $this->callbackUrl,
                'orderDetail' => $paymentData['order_detail'],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($this->urlBase . '/generate-qr', $body);

            $data = $response->json();

            Log::info('PagoFacil Generate QR Response', [
                'status' => $response->status(),
                'data' => $data,
                'body' => $body
            ]);

            if ($data['error'] === 0) {
                return $data['values'];
            }

            Log::error('PagoFacil Generate QR Error', ['response' => $data, 'body' => $body]);
            
            // Obtener mensaje de error más descriptivo
            $errorMessage = $data['message'] ?? 'Error al generar QR';
            if (isset($data['values']) && is_string($data['values'])) {
                $errorMessage .= ': ' . $data['values'];
            }
            
            throw new \Exception($errorMessage);

        } catch (\Exception $e) {
            Log::error('PagoFacil Generate QR Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Consultar estado de una transacción
     * 
     * @param string|null $pagofacilTransactionId ID de transacción de PagoFácil
     * @param string|null $companyTransactionId ID de transacción de la empresa
     * @return array Estado de la transacción
     */
    public function queryTransaction($pagofacilTransactionId = null, $companyTransactionId = null)
    {
        try {
            $token = $this->login();

            $body = [];
            if ($pagofacilTransactionId) {
                $body['pagofacilTransactionId'] = $pagofacilTransactionId;
            }
            if ($companyTransactionId) {
                $body['companyTransactionId'] = $companyTransactionId;
            }

            if (empty($body)) {
                throw new \Exception('Se requiere al menos un ID de transacción');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($this->urlBase . '/query-transaction', $body);

            $data = $response->json();

            if ($data['error'] === 0) {
                return $data['values'];
            }

            throw new \Exception($data['message'] ?? 'Error al consultar transacción');

        } catch (\Exception $e) {
            Log::error('PagoFacil Query Transaction Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
