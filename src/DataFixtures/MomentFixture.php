<?php 

namespace App\DataFixtures;

use App\Entity\Moment;
use App\Entity\Humeur;
use App\Entity\Tag;
use App\Entity\User;
use App\DataFixtures\HumeurFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MomentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Get dependencies (loaded automatically via getDependencies())
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $humeur = $manager->getRepository(Humeur::class)->findOneBy(['name' => 'joie']);
        
        // Create tags
        $tag1 = new Tag();
        $tag1->setName('nature');
        $manager->persist($tag1);

        $tag2 = new Tag();
        $tag2->setName('sport');
        $manager->persist($tag2);

        if (!$user) {
            throw new \Exception('User not found - make sure UserFixtures are loaded first');
        }
        
        if (!$humeur) {
            throw new \Exception('Humeur not found - make sure HumeurFixtures are loaded first');
        }

        // Create moments
        for ($i = 1; $i <= 3; $i++) {
            $moment = new Moment();
            $moment->setTitre('Moment '.$i);
            $moment->setDescription('Description '.$i);
            $moment->setCreateAt(new \DateTimeImmutable());
            $moment->setUser($user);
            $moment->setHumeur($humeur);
            $moment->addTag($tag1);
            $moment->addTag($tag2);  
            $manager->persist($moment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
        UserFixtures::class,
        HumeurFixture::class 
        ];
    }
}