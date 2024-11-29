<?php

namespace App\Util;
use App\Entity\Participant;
use App\Notification\SenderMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RegisterService
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    public function process(Participant $user, ?string $plainPassword, ?UploadedFile $uploadedFile, bool $isCreation, SenderMail $sender): void
    {
        // Hash the password if provided
        if ($plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        // Handle file upload
        if ($uploadedFile) {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            $uploadedFile->move('uploads/img_profile', $newFilename);
            $user->setFilename($newFilename);
        }

        // Persist the entity
        if ($isCreation) {
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();
        if($isCreation){
            $sender->sendNewUserNotificationToAdmin($user, $plainPassword);

        }
    }
}