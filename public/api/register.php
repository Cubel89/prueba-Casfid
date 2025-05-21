<?php

use App\Application\Command\CreateUserCommand;
use App\Application\DTO\UserDTO;
use App\Application\Handler\CreateUserHandler;
use App\Infrastructure\Persistence\Repository\MySQLiUserRepository;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true) ?: [];


    if (empty($requestData['username']) || empty($requestData['email']) || empty($requestData['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios (username, email, password)']);
        exit;
    }


    $userRepository = new MySQLiUserRepository();
    $command = new CreateUserCommand(
        $requestData['username'],
        $requestData['email'],
        $requestData['password']
    );

    try {
        $handler = new CreateUserHandler($userRepository);
        $user = $handler->handle($command);


        http_response_code(201); // Created
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'data' => UserDTO::fromEntity($user)
        ]);
    } catch (\Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}