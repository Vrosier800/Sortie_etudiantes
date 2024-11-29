<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;

class SortieService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }


    public function archiveSortieOrganisee($participant, $etat){
        $sortieOrganise = $participant->getOrganisateurs();
        if(!$sortieOrganise->isEmpty()){
            foreach ($sortieOrganise as $sortie) {
                $sortie->setEtat($etat);
                $this->em->persist($sortie);
            }
        }
    }

    public function supprimerInscritionSorties($participant){
        $sorties = $participant->getSorties();
        if(!$sorties->isEmpty()){
            foreach ($sorties as $sortie) {
                $sortie->removeParticipant($participant);
                $this->em->persist($sortie);
            }}
    }
}