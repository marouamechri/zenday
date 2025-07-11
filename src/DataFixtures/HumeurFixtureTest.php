<?php

namespace App\DataFixtures;

use App\Entity\Humeur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class HumeurFixtureTest extends Fixture
{
     public function load(ObjectManager $manager): void
    { $humeurs = [
            'joie',
        ];

        foreach ($humeurs as $name) {
            $humeur = new Humeur();
            $humeur->setName($name);
            $manager->persist($humeur);
        }

        $manager->flush();
    }
}
