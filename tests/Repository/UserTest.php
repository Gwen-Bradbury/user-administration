<?php

namespace App\Tests\Repository;

use App\Repository\User;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    private Connection $connection;
    private KernelBrowser $kernelBrowser;
    private User $user;

    public function setUp(): void
    {
        $this->kernelBrowser = static::createClient();
        $container = $this->kernelBrowser->getContainer();
        $this->connection = $container->get('database_connection');

        $this->connection->executeStatement('
            CREATE TABLE user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(255),
                email VARCHAR(255),
                password VARCHAR(255)
            )
        ');

        $this->user = new User($this->connection);
    }

    public function testGetsEmptyArrayIfNoUsers(): void
    {
        $this->assertSame([], $this->user->getAllUsers());
    }

    public function testItGetsAllUsers(): void
    {
        $this->connection->executeStatement("
            INSERT INTO user(username, email, password) 
            VALUES ('some username', 'some email', 'some password'), 
                   ('some other username', 'some other email', 'some other password')
        ");

        $this->assertSame(
            [
                ['id' => '1', 'username' => 'some username', 'email' => 'some email', 'password' => 'some password'],
                ['id' => '2', 'username' => 'some other username', 'email' => 'some other email',
                    'password' => 'some other password']
            ],
            $this->user->getAllUsers()
        );
    }

    public function testAddsAUser(): void
    {
        $this->user->addUser('some username', 'some email', 'some password');

        $this->assertSame(
            [['id' => '1', 'username' => 'some username', 'email' => 'some email', 'password' => 'some password']],
            $this->connection->fetchAllAssociative('SELECT * FROM user'),
        );
    }

    public function testGetsOneUser(): void
    {
        $this->connection->executeStatement("
            INSERT INTO user (username, email, password) 
            VALUES ('some username', 'some email', 'some password')
        ");

        $this->assertSame(
            ['id' => '1', 'username' => 'some username', 'email' => 'some email', 'password' => 'some password'],
            $this->user->getUser(1)
        );
    }

    public function testAUserGetsUpdated(): void
    {
        $this->connection->executeStatement("
            INSERT INTO user (username, email, password) 
            VALUES ('some username', 'some email', 'some password')
        ");

        $user = [
            'username' => 'some other username',
            'email' => 'some other email',
            'password' => 'some other password'
        ];
        $this->user->updateUser($user, 1);

        $this->assertSame(
            ['id' => '1', 'username' => 'some other username', 'email' => 'some other email',
                'password' => 'some other password'],
            $this->connection->fetchAssociative('SELECT * FROM user')
        );
    }

    public function testAUserGetsDeleted(): void
    {
        $this->connection->executeStatement("
            INSERT INTO user (username, email, password) 
            VALUES ('some username', 'some email', 'some password'), 
                   ('some other username', 'some other email', 'some other password')
        ");

        $this->user->deleteUser(1);

        $this->assertSame(
            [['id' => '2', 'username' => 'some other username', 'email' => 'some other email', 'password' => 'some other password']],
            $this->connection->fetchAllAssociative('SELECT * FROM user')
        );
    }
}