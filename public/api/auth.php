<?php

use App\Domain\Model\User;
use App\Infrastructure\Persistence\Repository\MySQLiUserRepository;
use Firebase\JWT\JWT;

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'POST') {

    $requestData = json_decode(file_get_contents('php://input'), true) ?: [];

    // Validar datos
    if (empty($requestData['username']) || empty($requestData['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios (username, password)']);
        exit;
    }

    // Verificar credenciales
    $userRepository = new MySQLiUserRepository();
    $user = $userRepository->findByUsername($requestData['username']);

    if (!$user || !$user->verifyPassword($requestData['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
        exit;
    }

    // Generar token JWT
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // Token válido por 1 hora
    $payload = [
        'user_id' => $user->getId(),
        'username' => $user->getUsername(),
        'iat' => $issuedAt,
        'exp' => $expirationTime
    ];

    $secretKey = getenv('JWT_SECRET');
    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    // Devolver token
    echo json_encode([
        'success' => true,
        'token' => $jwt,
        'expires' => $expirationTime,
        'user' => [
            'id' => $user->getId(),
            'username' => $user->getUsername()
        ]
    ]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}