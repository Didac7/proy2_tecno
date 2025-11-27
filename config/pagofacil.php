<?php

return [
    'url_base' => env('PAGOFACIL_URL_BASE', 'https://masterqr.pagofacil.com.bo/api/services/v2'),
    'token_service' => env('PAGOFACIL_TOKEN_SERVICE'),
    'token_secret' => env('PAGOFACIL_TOKEN_SECRET'),
    'callback_url' => env('PAGOFACIL_CALLBACK_URL'),
    'payment_method_id' => 4, // ID del método de pago QR
    'currency' => 2, // BOB (Bolivianos)
    'document_type' => 1, // CI
];
