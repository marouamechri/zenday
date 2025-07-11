<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    // Injection du service pour hasher les mots de passe
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Exemple de création de plusieurs utilisateurs
        $usersData = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin User',
                'password' => 'adminpass',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'email' => 'user1@example.com',
                'name' => 'User One',
                'password' => 'userpass1',
                'roles' => ['ROLE_USER']
            ],
            [
                'email' => 'user2@example.com',
                'name' => 'User Two',
                'password' => 'userpass2',
                'roles' => ['ROLE_USER']
            ],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            $user->setRoles($data['roles']);
            $user->setIsVerified(true);

            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
