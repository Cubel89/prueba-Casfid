<?php

namespace App\Domain\Repository;

use App\Domain\Model\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;

    public function save(User $user): User;

    public function update(User $user): bool;

    public function delete(int $id): bool;
}