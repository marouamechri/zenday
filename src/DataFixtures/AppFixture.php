<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
    }
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixture::class,
            HumeurFixture::class,
        ];
    }       
}
