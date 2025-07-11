<?php

namespace App\Tests\Controller;

use App\DataFixtures\TagFixture;
use App\DataFixtures\TagFixtureTest;
use App\DataFixtures\UserFixtures;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class TagControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $databaseTool;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get();
        
        // Charger les fixtures nécessaires
        $this->databaseTool->loadFixtures([
            UserFixtures::class,
            TagFixtureTest::class
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

        public function testGetTags(): void
    {
        $this->client->request(
            'GET',
            '/api/tags',
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        
        //$this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
    }

    public function testCreateTagAsAdmin(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'POST',
            '/api/tags',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'name' => 'nouveauTag'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('nouveauTag', $response['name']);
    }

    public function testCreateTagUnauthorized(): void
    {
        // Test sans token
        $this->client->request(
            'POST',
            '/api/tags',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'nouveauTag'])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateTag(): void
    {
        $token = $this->getAuthToken();
        $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['name' => 'amour']);

        $this->client->request(
            'PUT',
            '/api/tags/'.$tag->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token
            ],
            json_encode([
                'name' => 'tag1-modifie'
            ])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('tag1-modifie', $response['name']);
    }

    public function testDeleteTag(): void
    {
        $token = $this->getAuthToken();
        $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['name' => 'amour']);

        $this->client->request(
            'DELETE',
            '/api/tags/'.$tag->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(204);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}