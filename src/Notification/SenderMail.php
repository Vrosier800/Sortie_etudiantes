<?php

namespace App\Notification;

use App\Entity\Participant;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SenderMail
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly \Twig\Environment $twig,
    )
    {
    }

    public function sendNewUserNotificationToAdmin(Participant $participant, string $password) : void
    {
        // $content=$this->twig->render('email/newUser.html.twig', [])

        $message = new Email();
        $message->from('account@eni.fr')
            ->to($participant->getMail())
            ->subject('Nouveau compte')
            ->html('<h1>Voici votre mot de passe temporaire : '.$password.'</h1>');
        $this->mailer->send($message);
    }


}