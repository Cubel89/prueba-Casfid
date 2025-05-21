<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Verificar autenticación por token JWT
function getAuthorizationHeader() {
    $headers = null;

    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(
            array_map('ucwords', array_keys($requestHeaders)),
            array_values($requestHeaders)
        );

        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    return $headers;
}

function getBearerToken() {
    $headers = getAuthorizationHeader();

    // Verificar si el token está en el header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }

    // Verificar si el token está en los parámetros GET
    if (isset($_GET['token'])) {
        return $_GET['token'];
    }

    return null;
}

// Lista de endpoints que no requieren autenticación
$publicEndpoints = [
    '/api/auth'
];

// Verificar si la ruta actual es pública
$currentEndpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isPublicEndpoint = false;

foreach ($publicEndpoints as $endpoint) {
    if (strpos($currentEndpoint, $endpoint) === 0) {
        $isPublicEndpoint = true;
        break;
    }
}

// No verificar autenticación para endpoints públicos
if ($isPublicEndpoint) {
    return;
}

// Verificar token para endpoints protegidos
$jwt = getBearerToken();

if (!$jwt) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token de autenticación no proporcionado']);
    exit;
}

try {
    $secretKey = getenv('JWT_SECRET');
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

    // Agregar usuario autenticado a la solicitud
    $_SERVER['auth_user'] = $decoded;
} catch (\Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token inválido o expirado: ' . $e->getMessage()]);
    exit;
}