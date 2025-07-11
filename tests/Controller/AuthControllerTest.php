<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private $client;
    private $databaseTool;
    private $userRepository;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get();
        $this->userRepository = $container->get(UserRepository::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->databaseTool->loadFixtures([UserFixtures::class]);
    }

    public function testSuccessfulLogin(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user1@example.com',
                'password' => 'userpass1'
            ])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $response);
        $this->assertNotEmpty($response['token']);
    }

    public function testFailedLoginWithInvalidCredentials(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user1@example.com',
                'password' => 'wrongpassword'
            ])
        );
        $this->assertResponseStatusCodeSame(401);

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'nonexistent@example.com',
                'password' => 'somepassword'
            ])
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegisterNewUser(): void
    {
        $uniqueEmail = 'newuser_' . uniqid() . '@example.com';

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $uniqueEmail,
                'password' => 'Test1234!',
                'name' => 'New User'
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur créé. Un email de confirmation a été envoyé.', $response['message']);
        $this->assertEquals($uniqueEmail, $response['email']);

        $user = $this->userRepository->findOneBy(['email' => $uniqueEmail]);
        $this->assertNotNull($user);
        $this->assertFalse($user->isVerified());
    }

    public function testRegisterWithExistingEmail(): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user1@example.com',
                'password' => 'Test1234!',
                'name' => 'Existing User'
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Cet email est déjà utilisé.', $response['error']);
    }

    public function testPasswordResetFlow(): void
    {
        $email = 'user1@example.com';

        // 1. Demande de réinitialisation
        $this->client->request(
            'POST',
            '/api/forgot-password',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Si cet email existe, un lien a été envoyé.', $response['message']);

        $user = $this->userRepository->findOneBy(['email' => $email]);
        $this->assertNotNull($user->getResetToken());

        // 2. Réinitialisation avec le token
        $newPassword = 'NewPass123!';
        $this->client->request(
            'POST',
            '/api/reset-password',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'token' => $user->getResetToken(),
                'password' => $newPassword
            ])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Mot de passe mis à jour avec succès.', $response['message']);

        // 3. Vérification que le token est supprimé
        $updatedUser = $this->userRepository->findOneBy(['email' => $email]);
        $this->assertNull($updatedUser->getResetToken());
    }

    public function testForgotPasswordWithInvalidEmail(): void
    {
        $this->client->request(
            'POST',
            '/api/forgot-password',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'toto@toto.com'])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Si cet email existe, un lien a été envoyé.', $response['message']);
    }
}
