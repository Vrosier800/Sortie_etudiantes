<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $creee = new Etat();
        $creee->setLibelle('Créée');
        $manager->persist($creee);

        $ouverte = new Etat();
        $ouverte->setLibelle("Ouverte");
        $manager->persist($ouverte);

        $cloturee = new Etat();
        $cloturee->setLibelle("Clôturée");
        $manager->persist($cloturee);

        $activee = new Etat();
        $activee->setLibelle("Activité en cours");
        $manager->persist($activee);

        $passee = new Etat();
        $passee->setLibelle("Passée");
        $manager->persist($passee);

        $annulee = new Etat();
        $annulee->setLibelle("Annulée");
        $manager->persist($annulee);

        $archivee = new Etat();
        $archivee->setLibelle("Archivée");
        $manager->persist($archivee);

        $manager->flush();
    }
}
