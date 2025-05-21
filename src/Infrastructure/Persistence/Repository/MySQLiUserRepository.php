<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Persistence\Database\MySQLiConnection;

class MySQLiUserRepository implements UserRepositoryInterface
{
    private MySQLiConnection $db;

    public function __construct()
    {
        $this->db = MySQLiConnection::getInstance();
    }

    public function findById(int $id): ?User
    {
        $id = (int)$id;
        $sql = "SELECT * FROM users WHERE id = {$id} LIMIT 1";
        $row = $this->db->getRow($sql);

        if (!$row) {
            return null;
        }

        return $this->createUserFromRow($row);
    }

    public function findByUsername(string $username): ?User
    {
        $username = $this->db->escape($username);
        $sql = "SELECT * FROM users WHERE username = '{$username}' LIMIT 1";
        $row = $this->db->getRow($sql);

        if (!$row) {
            return null;
        }

        return $this->createUserFromRow($row);
    }

    public function findByEmail(string $email): ?User
    {
        $email = $this->db->escape($email);
        $sql = "SELECT * FROM users WHERE email = '{$email}' LIMIT 1";
        $row = $this->db->getRow($sql);

        if (!$row) {
            return null;
        }

        return $this->createUserFromRow($row);
    }

    public function save(User $user): User
    {
        $username = $this->db->escape($user->getUsername());
        $email = $this->db->escape($user->getEmail());
        $password = $this->db->escape($user->getPassword());

        $sql = "INSERT INTO users (username, email, password) VALUES ('{$username}', '{$email}', '{$password}')";

        $this->db->query($sql);

        if ($this->db->affectedRows() <= 0) {
            throw new \RuntimeException("No se pudo guardar el usuario");
        }

        $user->setId($this->db->lastInsertId());

        return $user;
    }

    public function update(User $user): bool
    {
        $id = (int)$user->getId();
        $email = $this->db->escape($user->getEmail());
        $password = $this->db->escape($user->getPassword());

        $sql = "UPDATE users SET email = '{$email}', password = '{$password}' WHERE id = {$id}";

        $this->db->query($sql);

        return $this->db->affectedRows() > 0;
    }

    public function delete(int $id): bool
    {
        $id = (int)$id;
        $sql = "DELETE FROM users WHERE id = {$id}";

        $this->db->query($sql);

        return $this->db->affectedRows() > 0;
    }

    private function createUserFromRow(array $row): User
    {
        $user = new User(
            $row['username'],
            $row['email'],
            $row['password']
        );

        $user->setId($row['id']);

        return $user;
    }
}