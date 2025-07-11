<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtureTest extends Fixture
{
    public function load(ObjectManager $manager): void
    { $tags = [
            'amour'
        ];

        foreach ($tags as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $manager->persist($tag);
        }
            $manager->flush();
    }
}
