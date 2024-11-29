<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Repository\CampusRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher){}

    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create('fr_FR');
        $campus = $manager->getRepository(Campus::class)->findAll();



        for ($i = 0; $i < 10; $i++) {

            $formattedPhoneNumber =
                '0' .
                $faker->numberBetween(1, 9) .

                $faker->numberBetween(0, 9) .
                $faker->numberBetween(0, 9) .

                $faker->numberBetween(0, 9) .
                $faker->numberBetween(0, 9) .

                $faker->numberBetween(0, 9) .
                $faker->numberBetween(0, 9) .

                $faker->numberBetween(0, 9) .
                $faker->numberBetween(0, 9);

            $participant = new Participant();
          
            $participant->setMail($faker->email);
            $participant->setPassword($this->hasher->hashPassword($participant, 'password'));
            $participant->setNom($faker->lastName);
            $participant->setPrenom($faker->firstName);
            $participant->setPseudo($faker->optional(0.7)->userName());
            $participant->setTelephone($faker->randomElement([$formattedPhoneNumber, null]));
            $participant->setAdministrateur(false);
            $participant->setCampus($faker->randomElement($campus));
            $participant->setActif($faker->boolean(80));
            $participant->setRoles(["ROLE_USER"]);

            $manager->persist($participant);
        }


        $admin1 = new Participant();
        $admin1->setMail('erwan@eni.fr');
        $admin1->setPassword($this->hasher->hashPassword($admin1, 'erwan'));
        $admin1->setNom('Cousseau');
        $admin1->setPrenom('Erwan');
        $admin1->setPseudo('WanWanDu49');
        $admin1->setTelephone('0645251454');
        $admin1->setAdministrateur(true);
        $admin1->setCampus($faker->randomElement($campus));
        $admin1->setActif(true);
        $admin1->setRoles(["ROLE_SUPER_ADMIN"]);

        $manager->persist($admin1);

        $pasAdmin = new Participant();
        $pasAdmin->setMail('antoine@eni.fr');
        $pasAdmin->setPassword($this->hasher->hashPassword($pasAdmin, 'antoine'));
        $pasAdmin->setNom('Pas');
        $pasAdmin->setPrenom('Admin');
        $pasAdmin->setPseudo('PasAdmin');
        $pasAdmin->setTelephone('0645251454');
        $pasAdmin->setAdministrateur(false);
        $pasAdmin->setCampus($faker->randomElement($campus));
        $pasAdmin->setActif(true);
        $pasAdmin->setRoles(["ROLE_USER"]);

        $manager->persist($pasAdmin);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CampusFixtures::class,
        ];
    }
}
