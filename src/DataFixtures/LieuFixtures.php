<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $ville =  $manager->getRepository(Ville::class)->findAll();

        $types = [
            'Le Rivage',
            'L\'Eclipse',
            'Le Bosquet',
            'La Clairière',
            'L\'Horizon',
            'Le Rocher',
            'L\'Aube',
            'La Brise',
            'Les Alizés',
            'Le Jardin',
            'Le Bitume',
            'Euh L\'tiot stagiaire',
            'La Corse qui dépote',
            'Chemilly quelque part'
        ];


        for ($i = 0; $i < 30; $i++) {
            $lieu = new Lieu();
            $lieu->setVille($faker->randomElement($ville));
            $lieu->setNom($faker->randomElement($types));
            $lieu->setRue($faker->streetAddress());
            $lieu->setLongitude($faker->longitude());
            $lieu->setLatitude($faker->latitude());
            $manager->persist($lieu);
        }
      
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}
