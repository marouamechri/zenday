<?php

namespace App\Tests\Controller;

use App\DataFixtures\HumeurFixture;
use App\DataFixtures\MomentFixtures;
use App\DataFixtures\TagFixture;
use App\DataFixtures\UserFixtures;
use App\Entity\Humeur;
use App\Entity\Moment;
use App\Entity\Tag;
use App\Entity\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MomentControllerTest extends WebTestCase
{
    private $client;
    private $databaseTool;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get();

        // Load ALL required fixtures first
        $this->databaseTool->loadFixtures([
            UserFixtures::class,
            HumeurFixture::class,
            TagFixture::class,
            MomentFixtures::class
        ]);

    }

    public function testFixtureSetup()
    {
        // Verify humeur exists
        $humeur = $this->entityManager
            ->getRepository(Humeur::class)
            ->findOneBy(['name' => 'joie']);
        
        $this->assertNotNull($humeur, 'Humeur "joie" not found in database');
        
        // Verify user exists
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'user1"@example.com']);
        
        $this->assertNotNull($user, 'User not found in database');
    }

    private function getAuthToken(string $email = 'user1@example.com', string $password = 'userpass1'): string
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
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $response);
        
        return $response['token'];
    }

    public function testGetUserMoments(): void
    {
        $token = $this->getAuthToken();

        $this->client->request(
            'GET',
            '/api/moments/user',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseIsSuccessful();
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(3, $response);
        $this->assertEquals('Moment 1', $response[0]['titre']);
    }

    public function testCreateMoment(): void
    {
        $this->databaseTool->loadFixtures([UserFixtures::class]);
        $token = $this->getAuthToken();

        $this->client->request(
            'POST',
            '/api/moments',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
                'HTTP_ACCEPT' => 'application/json'
            ],
            json_encode([
                'titre' => 'New Moment',
                'description' => 'Test description',
                'humeur' => 'joie',
                'tags' => ['amour']
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('New Moment', $response['titre']);
    }

    public function testUpdateMoment(): void
    {
        $this->databaseTool->loadFixtures([UserFixtures::class,MomentFixtures::class]);
        $token = $this->getAuthToken();

        $moment = $this->entityManager->getRepository(Moment::class)
            ->findOneBy(['titre' => 'Moment 1']);

        $this->client->request(
            'PUT',
            '/api/moments/'.$moment->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
                'HTTP_ACCEPT' => 'application/json'
            ],
            json_encode([
                'titre' => 'Updated Moment',
                'humeur' => 'joie',
                'tags' => ['amour']
            ])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Moment', $response['titre']);
    }

    public function testDeleteMoment(): void
    {
        $this->databaseTool->loadFixtures([UserFixtures::class, MomentFixtures::class]);
        $token = $this->getAuthToken();

        $moment = $this->entityManager->getRepository(Moment::class)
            ->findOneBy(['titre' => 'Moment 1']);

        $this->client->request(
            'DELETE',
            '/api/moments/'.$moment->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull(
            $this->entityManager->getRepository(Moment::class)->find($moment->getId())
        );
    }

    public function testGetLatestMoment(): void
    {
        $this->databaseTool->loadFixtures([UserFixtures::class, MomentFixtures::class]);
        $token = $this->getAuthToken();

        $this->client->request(
            'GET',
            '/api/moments/user/latest',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('titre', $response);
        $this->assertEquals('Moment 3', $response['titre']); // Latest should be Moment 3
    }

    public function testUnauthorizedAccess(): void
    {
        $this->client->request('GET', '/api/moments/user');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetMomentsCount(): void
    {
        $this->databaseTool->loadFixtures([UserFixtures::class, MomentFixtures::class]);
        $token = $this->getAuthToken();

        $this->client->request(
            'GET',
            '/api/moments/user/count',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(3, $response['count']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}