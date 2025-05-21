<?php

namespace App\Application\DTO;

use App\Domain\Model\User;

class UserDTO
{
    public int $id;
    public string $username;
    public string $email;
    public string $createdAt;

    public static function fromEntity(User $user): self
    {
        $dto = new self();
        $dto->id = $user->getId();
        $dto->username = $user->getUsername();
        $dto->email = $user->getEmail();
        $dto->createdAt = $user->getCreatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}