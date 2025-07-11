<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    { $tags = [
            'gratitude',
            'détente',
            'sourire',
            'famille',
            'amis',
            'nature',
            'musique',
            'sport',
            'succès',
            'créativité',
            'lecture',
            'repos',
            'voyage'
        ];

        foreach ($tags as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $manager->persist($tag);
        }
            $manager->flush();
    }
}
