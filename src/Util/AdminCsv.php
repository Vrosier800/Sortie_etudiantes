<?php

namespace App\Util;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Notification\SenderMail;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminCsv
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $hasher;
    private ValidatorInterface $validator;
    private SenderMail $senderMail;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, ValidatorInterface $validator, SenderMail $senderMail)
    {
        $this->entityManager = $entityManager;
        $this->hasher = $hasher;
        $this->validator = $validator;
        $this->senderMail = $senderMail;
    }

    public function importationFichier(string $chemin)
    {

        $csv = Reader::createFromPath($chemin);
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(";");

        $stmt = new Statement();
        $enregistrements = $stmt->process($csv);

        $erreurs = [];

        foreach ($enregistrements as $i => $enregistrement) {
            $participants = new Participant();

            if (!isset($enregistrement['campus'])) {
                $erreurs[] = "Ligne " . ($i + 1) . ": La colonne 'campus' est manquante.";
                continue;
            }


            $campus = $this->entityManager->getRepository(Campus::class)->findOneBy(['nom' => $enregistrement['campus']]);

            if (!$campus) {
                $erreurs[] = "Ligne " . ($i + 1) . ": Campus \"" . $enregistrement['campus'] . "\" introuvable.";
                continue;
            }

            $existingParticipantByMail = $this->entityManager->getRepository(Participant::class)->findOneBy(['mail' => $enregistrement['mail']]);
            if ($existingParticipantByMail) {
                $erreurs[] = "Ligne " . ($i + 1) . ": L'email \"" . $enregistrement['mail'] . "\" est déjà utilisé.";
                continue;
            }

            $participants->setMail($enregistrement['mail']);
            $participants->setRoles(json_decode($enregistrement['roles'], true));
            $password = $this->hasher->hashPassword($participants, $enregistrement['nom'] . $enregistrement['prenom']);
            $participants->setPassword($password);
            $participants->setNom($enregistrement['nom']);
            $participants->setPrenom($enregistrement['prenom']);
            $participants->setAdministrateur(filter_var($enregistrement['administrateur'], FILTER_VALIDATE_BOOLEAN));
            $participants->setActif(filter_var($enregistrement['actif'], FILTER_VALIDATE_BOOLEAN));
            $participants->setCampus($campus);

            $violations = $this->validator->validate($participants);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $erreurs[] = "Ligne " . ($i + 1) . ": " . $violation->getMessage();
                }
            } else {
                $this->entityManager->persist($participants);
                $this->entityManager->flush();
                $this->senderMail->sendNewUserNotificationToAdmin($participants, $enregistrement['nom'] . $enregistrement['prenom']);
            }
        }
        return $erreurs;
    }
}