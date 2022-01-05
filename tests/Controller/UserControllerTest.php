<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

class UserControllerTest extends WebTestCase
{
    private Connection $connection;
    private KernelBrowser $kernelBrowser;

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
    }

    public function testItRendersAllUsers(): void
    {
        $this->connection->executeStatement("
            INSERT INTO user (username, email, password) 
            VALUES ('some username', 'some email', 'some password'), 
                   ('some other username', 'some other email', 'some other password')
        ");

        $this->kernelBrowser->request('GET', '/all-users');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('some password', $this->kernelBrowser->getResponse()->getContent());
    }

    public function testItRendersAddUserForm(): void
    {
        $this->kernelBrowser->request('GET', '/add-user-form');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Add A User', $this->kernelBrowser->getResponse()->getContent());
    }

    public function testItAddsUser(): void
    {
        $this->kernelBrowser->request(
            'POST',
            '/add-user', ['username' => 'some username', 'email' => 'some email', 'password' => 'some password']
        );

        $user = $this->connection->fetchAssociative('SELECT username, email, password FROM user WHERE id = 1');
        $this->assertSame('some username', $user['username']);
        $this->assertSame('some email', $user['email']);

        $factory = new PasswordHasherFactory(['common' => ['algorithm' => 'sha256']]);
        $password = $factory->getPasswordHasher('common')->hash('some password');
        $this->assertSame($password, $user['password']);
        $this->assertResponseRedirects('/all-users');
    }

    public function testItRendersEditUserForm(): void
    {
        $this->connection->executeStatement("
            INSERT INTO user (username, email, password) 
            VALUES ('some username', 'some email', 'some password')
        ");

        $this->kernelBrowser->request('GET', '/edit-user/1');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('some password', $this->kernelBrowser->getResponse()->getContent());
    }

    public function testItUpdatesUser(): void
    {
        $this->connection->executeStatement("
            INSERT INTO user (username, email, password) 
            VALUES ('some username', 'some email', 'some password')
        ");

        $this->kernelBrowser->request(
            'POST',
            '/update-user/1', [
                'username' => 'some other username',
                'email' => 'some other email',
                'password' => 'some other password'
            ]
        );

        $this->assertSame([[
                'id' => '1',
                'username' => 'some other username',
                'email' => 'some other email',
                'password' => 'some other password'
            ]], $this->connection->fetchAllAssociative('SELECT * FROM user'));
        $this->assertResponseRedirects('/all-users');
    }

    public function testItDeletesUser(): void
    {
        $this->connection->executeStatement("
            INSERT INTO user (username, email, password) 
            VALUES ('some username', 'some email', 'some password')
        ");

        $this->kernelBrowser->request('GET', '/delete-user/1');

        $this->assertSame([], $this->connection->fetchAllAssociative('SELECT * FROM user'));
        $this->assertResponseRedirects('/all-users');
    }
}