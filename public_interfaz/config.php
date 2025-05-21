<?php
define('API_BASE_URL', 'http://servidor_apache:80/api');
define('SESSION_TOKEN_KEY', 'books_api_token');
define('APP_NAME', 'Gestión de Libros');

function callAPI($method, $endpoint, $data = [], $token = null) {
    $url = API_BASE_URL . $endpoint;

    $curl = curl_init();
    $headers = ['Content-Type: application/json'];

    // Añadir token de autenticación si existe
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];

    //Añadir datos
    if ($method !== 'GET' && !empty($data)) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return [
            'success' => false,
            'message' => 'Error en la conexión: ' . $error,
            'code' => 500
        ];
    }

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => json_decode($response, true),
        'code' => $httpCode
    ];
}

function isAuthenticated() {
    return isset($_SESSION[SESSION_TOKEN_KEY]) && !empty($_SESSION[SESSION_TOKEN_KEY]);
}

// Función para verificar si un token es válido
function validateToken($token) {
    $result = callAPI('GET', '/books?limit=1', [], $token);
    return $result['success'];
}

// Función para redirigir si no está autenticado
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }

    // Verificar si el token es válido
    if (!validateToken($_SESSION[SESSION_TOKEN_KEY])) {
        // Token inválido, destruir sesión y redirigir
        session_destroy();
        header('Location: login.php?error=token_expired');
        exit;
    }
}