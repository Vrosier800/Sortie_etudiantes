<?php

namespace App\EventSubscriber;

use App\Entity\Sortie;
use App\Message\CheckDateMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;


#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Sortie::class)]
class SortieUpdate
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function postUpdate(Sortie $entity, PostUpdateEventArgs $args): void
    {
        if (!$entity instanceof Sortie) {
            return;
        }

        $now = new \DateTimeImmutable();

       if($entity->getEtat()->getLibelle() == 'Créée' || $entity->getEtat()->getLibelle() == 'Ouverte'  ){
            $dateLimite = $entity->getDateLimiteInscription();
            if ($dateLimite) {
                $delayCloture = max(0, ($dateLimite->getTimestamp() - $now->getTimestamp()) * 1000);
                $clotureMessage = new CheckDateMessage($entity->getId());
                $this->messageBus->dispatch($clotureMessage, [new DelayStamp($delayCloture)]);


            }
        }
        $dateDebut = $entity->getDateHeureDebut();
        if($dateDebut){
            $delayCloture = max(0, ($dateDebut->getTimestamp() - $now->getTimestamp()) * 1000);
            $enCoursMessage = new CheckDateMessage($entity->getId(), $dateDebut);
            $this->messageBus->dispatch($enCoursMessage, [new DelayStamp($delayCloture)]);
        }

    }
}