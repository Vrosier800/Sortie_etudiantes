<?php

namespace App\DataFixtures;


use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $evenement = ["Théatre", "Cinéma", "Musée", "Concert", "Expo", "Atelier", "Bar", "Boîte de nuit", "Circuit"];
        $lieu = $manager->getRepository(Lieu::class)->findAll();
        $organisateurs = $manager->getRepository(Participant::class)->findAll();
        $etat = $manager->getRepository(Etat::class)->findBy(["libelle" => "Ouverte"]);
        $siteOrganisateur = $manager->getRepository(Campus::class)->findAll();

        for ($i = 0; $i < 10; $i++) {
            $dateLimiteInscription = $faker->dateTimeBetween("now", "+3 month");
            $dateHeureDebut = $faker->dateTimeBetween(
                $dateLimiteInscription,
                $dateLimiteInscription->format('Y-m-d H:i:s') . ' +15 days'
            );

            $sortie = new Sortie();
            $sortie->setLieu($faker->randomElement($lieu));
            $sortie->setNom($faker->randomElement($evenement));
            $sortie->setDuree($faker->numberBetween(1,3));
            $sortie->setInfosSortie($faker->paragraph());
            $sortie->setDateHeureDebut($dateHeureDebut);
            $sortie->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimiteInscription));
            $sortie->setOrganisateur($faker->randomElement($organisateurs));
            $sortie->setSiteOrganisateur($faker->randomElement($siteOrganisateur));
            $sortie->setEtat(($etat[0]));
            $participants = [];
            for ($j = 0; $j < 10; $j++) {
                $participant = $faker->optional(0.5)->randomElement($organisateurs);
                if ($participant) {
                    $participants[] = $participant;
                    $sortie->addParticipant($participant);
                }
            }
            $participantCount = count($participants);
            $sortie->setNbInscriptionsMax($faker->numberBetween($participantCount + 1, $participantCount + 10));

            $manager->persist($sortie);
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            LieuFixtures::class,
            ParticipantFixtures::class,
            CampusFixtures::class,
            EtatFixtures::class
        ];
    }
}