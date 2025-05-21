<?php

namespace App\Application\Handler;

use App\Application\Command\CreateUserCommand;
use App\Domain\Model\User;
use App\Domain\Repository\UserRepositoryInterface;

class CreateUserHandler
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(CreateUserCommand $command): User
    {
        // Verificar si el usuario ya existe
        $existingUserByUsername = $this->userRepository->findByUsername($command->username);
        if ($existingUserByUsername !== null) {
            throw new \RuntimeException("El nombre de usuario '{$command->username}' ya está en uso");
        }

        $existingUserByEmail = $this->userRepository->findByEmail($command->email);
        if ($existingUserByEmail !== null) {
            throw new \RuntimeException("El email '{$command->email}' ya está registrado");
        }


        if (!filter_var($command->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("El email proporcionado no es válido");
        }


        if (strlen($command->password) < 6) {
            throw new \InvalidArgumentException("La contraseña debe tener al menos 6 caracteres");
        }


        $hashedPassword = password_hash($command->password, PASSWORD_DEFAULT);
        $user = new User($command->username, $command->email, $hashedPassword);

        return $this->userRepository->save($user);
    }
}