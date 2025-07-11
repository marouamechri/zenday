<?php

namespace App\Tests\Controller;

use App\Entity\Humeur;
use App\DataFixtures\HumeurFixtureTest;
use App\DataFixtures\UserFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HumeurControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $repository;
    private $databaseTool;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $this->entityManager->getRepository(Humeur::class);
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get();
        
        // Load necessary fixtures
        $this->databaseTool->loadFixtures([
            UserFixtures::class,
            HumeurFixtureTest::class
        ]);
    }

    private function getAuthToken(string $email = 'admin@example.com', string $password = 'adminpass'): string
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password
            ])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        return $data['token'];
    }

    public function testGetAll(): void
    {
        $this->client->request('GET', '/api/humeur');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $response); // Only 'joie' from HumeurFixtureTest
        $this->assertEquals('joie', $response[0]['name']);
    }

    public function testGetOne(): void
    {
        $humeur = $this->repository->findOneBy(['name' => 'joie']);
        
        $this->client->request('GET', '/api/humeur/'.$humeur->getId());
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('joie', $response['name']);
    }

    public function testCreateWithAdmin(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'POST',
            '/api/humeur',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token
            ],
            json_encode(['name' => 'NewHumeur'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('NewHumeur', $response['name']);
    }

    public function testCreateUnauthenticated(): void
    {
        $this->client->request(
            'POST',
            '/api/humeur',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'NewHumeur'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdate(): void
    {
        $token = $this->getAuthToken();
        $humeur = $this->repository->findOneBy(['name' => 'joie']);

        $this->client->request(
            'PUT',
            '/api/humeur/'.$humeur->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token
            ],
            json_encode(['name' => 'UpdatedHumeur'])
        );

        $this->assertResponseIsSuccessful();
        
        $updated = $this->repository->find($humeur->getId());
        $this->assertEquals('UpdatedHumeur', $updated->getName());
    }

    public function testDelete(): void
    {
        $token = $this->getAuthToken();
        $humeur = $this->repository->findOneBy(['name' => 'joie']);

        $this->client->request(
            'DELETE',
            '/api/humeur/'.$humeur->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->repository->find($humeur->getId()));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}