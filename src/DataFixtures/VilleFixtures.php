<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $villeErwan = new Ville();
        $villeErwan->setNom('Chemillé-en-Anjou');
        $villeErwan->setCodePostal('49120');
        $manager->persist($villeErwan);

        $villeVictor = new Ville();
        $villeVictor->setNom('Amiens');
        $villeVictor->setCodePostal('80000');
        $manager->persist($villeVictor);

        $villeAleks = new Ville();
        $villeAleks->setNom('Trop-Mystérieux');
        $villeAleks->setCodePostal('87450');
        $manager->persist($villeAleks);

        $villeSacha = new Ville();
        $villeSacha->setNom('Jose-plus-demander');
        $villeSacha->setCodePostal('20000');
        $manager->persist($villeSacha);

        $manager->flush();
    }
}
