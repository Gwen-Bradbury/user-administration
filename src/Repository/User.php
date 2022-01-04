<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class User
{
    public function __construct(private Connection $connection)
    {
    }

    public function getAllUsers(): array
    {
        return $this->connection->fetchAllAssociative('SELECT * FROM user');
    }

    public function addUser(string $userUsername, string $userEmail, string $userPassword): void
    {
        $this->connection->executeStatement(
            'INSERT INTO user (username, email, password) VALUES (:username, :email, :password)',
            ['username' => $userUsername, 'email' => $userEmail, 'password' => $userPassword,]
        );
    }

    public function getUser(int $id): array
    {
        return $this->connection->fetchAssociative('SELECT * FROM user WHERE id = :id', ['id' => $id]);
    }

    public function updateUser(array $user, int $id): void
    {
        $this->connection->executeStatement(
            'UPDATE user SET username = :username, email = :email, password = :password WHERE id = :id',
            ['id' => $id, 'username' => $user['username'], 'email'  => $user['email'], 'password'  => $user['password']]
        );
    }

    public function deleteUser(int $id): void
    {
        $this->connection->executeStatement('DELETE FROM user WHERE user.id = :id', ['id' => $id]);
    }
}