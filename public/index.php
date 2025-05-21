<?php

use App\Infrastructure\Persistence\Database\MySQLiConnection;

//Indicar que solo se aceptan respuestas en formato JSON
header('Content-Type: application/json');

// Incluir el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

//Cargar variables de entorno
try {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable('/var/www');
    $dotenv->load();
} catch (Exception $e) {
    //Si falla, log del error y usar la solución temporal
    error_log("Error cargando .env: " . $e->getMessage());

    // Solución de respaldo
    putenv('DB_HOST=dbmysql');
    putenv('DB_NAME=db_local');
    putenv('DB_USER=user_local');
    putenv('DB_PASSWORD=8egp5AGU4KQC1qm8Z3pD7X8L');
    putenv('JWT_SECRET=Z8hMuvFYe5E3kNxpqDjWfSgRc7V2yXbL');
}

// Capturar todas las excepciones y devolverlas como respuestas JSON
set_exception_handler(function ($exception) {
    $statusCode = $exception instanceof \RuntimeException ? 400 : 500;

    $response = [
        'success' => false,
        'message' => $exception->getMessage(),
        'code' => $statusCode
    ];


    error_log($exception->getMessage() . ' ' . $exception->getTraceAsString());

    http_response_code($statusCode);
    echo json_encode($response);
    exit;
});

// Obtener la ruta solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Rutas de la API
if (isset($uri[1]) && $uri[1] === 'api') {
    // Rutas de libros: /api/books
    if (isset($uri[2]) && $uri[2] === 'books') {
        require_once __DIR__ . '/api/books.php';
    }
    // Rutas de autenticación: /api/auth
    elseif (isset($uri[2]) && $uri[2] === 'auth') {
        require_once __DIR__ . '/api/auth.php';
    }
    // Rutas de registro: /api/register
    elseif (isset($uri[2]) && $uri[2] === 'register') {
        require_once __DIR__ . '/api/register.php';
    }
    // Ruta no encontrada
    else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
        exit;
    }
}
// Página principal
else {
    echo json_encode([
        'success' => true,
        'message' => 'API de Gestión de Libros',
        'endpoints' => [
            '/api/books' => 'CRUD de libros',
            '/api/auth' => 'Autenticación',
            '/api/register' => 'Registro de usuarios'
        ]
    ]);
}