<?php

namespace App\MessageHandler;

use App\Entity\Sortie;
use App\Message\ArchiveSortieMessage;
use App\Message\CheckDateMessage;
use App\Message\PasseeSortieMessage;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class CheckDateMessageHandler
{
    private $messageBus;
    private EntityManagerInterface $em;
    private EtatRepository $etatRepository;

    public function __construct(EntityManagerInterface $em, EtatRepository $etatRepository,MessageBusInterface $messageBus){
        $this->em = $em;
        $this->etatRepository = $etatRepository;
        $this->messageBus = $messageBus;
    }

    public function __invoke(CheckDateMessage $checkDateMessage){
        $now = new \DateTimeImmutable();
        $creee = $this->etatRepository->findOneBy(['libelle' => 'Créée']);
        $ouverte = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $cloture = $this->etatRepository->findOneBy(['libelle' => 'Clôturée']);
        $archive = $this->etatRepository->findOneBy(['libelle' => 'Archivée']);
        $enCours = $this->etatRepository->findOneBy(['libelle' => 'Activité en cours']);
        $passee= $this->etatRepository->findOneBy(['libelle' => 'Passée']);


        $entityId=$checkDateMessage->getEntityId();
        $sortie=$this->em->getRepository(Sortie::class)->find($entityId);

        if( $sortie->getEtat()->getLibelle()== $cloture->getLibelle()||
            $sortie->getEtat()->getLibelle()== $enCours->getLibelle()||
            $sortie->getEtat()->getLibelle()== $passee->getLibelle()){

            $heureDebutSortie = $sortie->getDateHeureDebut();
            $duree = $sortie->getDuree();
            $heureFinSortie = (clone $heureDebutSortie)->modify('+' . $duree . ' minute');
        }

        if($sortie) {
            $etat = $sortie->getEtat()->getLibelle();
            switch ($etat) {
                case $creee->getLibelle():
                case $ouverte->getLibelle():
                    $this->createOrOpen($sortie, $now, $cloture, $archive);
                    break;
                case $cloture->getLibelle():
                    $this->closed($sortie, $now, $enCours,$heureFinSortie);
                    break;
                case $enCours->getLibelle() :
                    $this->expired($sortie, $passee,$heureFinSortie,$now);
                    break;
                case $passee->getLibelle() :
                    $this->archive($sortie, $archive,$heureFinSortie,$now);
                    break;
            }
            $this->em->flush();
        }
    }
    private function createOrOpen($sortie,$now,$cloture,$archive)
    {
        if(count($sortie->getParticipants())>0 && $sortie->getDateLimiteInscription() <= $now){
            $sortie->setEtat($cloture);
        }else if ($sortie->getDateLimiteInscription() <= $now){
            $sortie->setEtat($archive);
        }
    }

    private function closed($sortie,$now,$enCours,$heureFinSortie)
    {
        if($sortie->getDateHeureDebut() <= $now) {
            $sortie->setEtat($enCours);
            $delayPasse = max(0, ($heureFinSortie->getTimestamp() - $now->getTimestamp()) * 1000);
            $passeeMessage = new CheckDateMessage($sortie->getId());
            $this->messageBus->dispatch($passeeMessage, [new DelayStamp($delayPasse)]);
        }
    }

    private function expired($sortie,$passee,$heureFinSortie,$now){
        if($heureFinSortie >= $now){
            $sortie->setEtat($passee);
            //$archiveDelay = 30 * 24 * 60 * 60 * 1000; // Pour un mois en millisecondes
            $archiveDelay = 1 * 60 * 1000;
            $archiveMessage = new CheckDateMessage($sortie->getId());
            $this->messageBus->dispatch($archiveMessage, [new DelayStamp($archiveDelay)]);
        }
    }

    private function archive($sortie, $archive,$heureFinSortie,$now){
        if($heureFinSortie < $now){
            $sortie->setEtat($archive);
        }
    }
}