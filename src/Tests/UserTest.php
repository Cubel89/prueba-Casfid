<?php

use App\Domain\Model\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreateUser()
    {
        $username = 'Paco1';
        $email = 'paco1@cubel.dev';
        $password = password_hash('inventado123', PASSWORD_DEFAULT);

        $user = new User($username, $email, $password);

        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertNull($user->getId()); // Sin ID hasta que se guarde
    }

    public function testVerifyPassword()
    {
        // Crear un usuario con contraseña hasheada
        $username = 'Paco2';
        $email = 'paco2@cubel.dev';
        $plainPassword = 'inventado123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $user = new User($username, $email, $hashedPassword);

        // Verificar contraseña correcta
        $this->assertTrue($user->verifyPassword($plainPassword));

        // Verificar contraseña incorrecta
        $this->assertFalse($user->verifyPassword('inventado1234'));
    }

    public function testUpdateUser()
    {
        // Datos iniciales
        $user = new User('Paco3', 'paco3@cubel.dev', password_hash('inventado123', PASSWORD_DEFAULT));
        $user->setId(5); // ID simulado

        // Cambiar email
        $nuevoEmail = 'pacopaco@cubel.dev';
        $user->setEmail($nuevoEmail);
        $this->assertEquals($nuevoEmail, $user->getEmail());

        // Cambiar contraseña
        $nuevaContraseña = password_hash('inventado1234', PASSWORD_DEFAULT);
        $user->setPassword($nuevaContraseña);
        $this->assertEquals($nuevaContraseña, $user->getPassword());

        // Comprobar que el ID no cambia
        $this->assertEquals(5, $user->getId());
    }
}