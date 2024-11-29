<?php

namespace App\EventSubscriber;

use App\Entity\Sortie;
use App\Message\CheckDateMessage;
use App\Message\ArchiveSortieMessage;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Sortie::class)]
class SortieSubscriber
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function postPersist(Sortie $entity, PostPersistEventArgs $args): void
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

            $dateDebut = $entity->getDateHeureDebut();
            if($dateDebut){
                $delayEnCours = max(0, ($dateDebut->getTimestamp() - $now->getTimestamp()) * 1000);
                $enCoursMessage = new CheckDateMessage($entity->getId());
                $this->messageBus->dispatch($enCoursMessage, [new DelayStamp($delayEnCours)]);
            }
        }
    }
}