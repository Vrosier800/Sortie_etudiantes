<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Detection\MobileDetect;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/sortie')]
final class SortieController extends AbstractController
{
    private $mobileDetect;

    public function __construct(MobileDetect $mobileDetect) {
        $this->mobileDetect = $mobileDetect;
    }

    /**
     * @throws \DateMalformedStringException
     */
    #[Route(name: 'sortie_show', methods: ['GET', 'POST'])]
    public function show(
        SortieRepository $sortieRepository,
        CampusRepository $campusRepository,
        Request          $request
    ): Response
    {
        $user = $this->getUser();
        $campusUtilisateur = $user->getCampus();

        $sorties = $sortieRepository->findBy([], ['dateLimiteInscription' => 'ASC']);

        // Créer un formulaire pour chaque sortie
        $forms = [];
        foreach ($sorties as $sortie) {
            $forms[$sortie->getId()] = $this->createForm(AnnulationType::class, null, [
                'action' => $this->generateUrl('sortie_annuler', ['id' => $sortie->getId()]),
            ])->createView();
        }
        $campus = $campusRepository->findBy([], ['nom' => 'ASC']);

        if ($request->isMethod('POST')) {
            $campusFilter = $request->request->get('campus');
            $searchFilter = $request->request->get('search');
            $dateDebutFilter = $request->request->get('dateDebut');
            $dateFinFilter = $request->request->get('dateFin');

            $campusFilter = ($campusFilter !== 'null' && $campusFilter !== '') ? (int)$campusFilter : $campusUtilisateur->getId();

            $sorties = $sortieRepository->findByFilters(
                $searchFilter,
                $campusFilter,
                $dateDebutFilter ? new \DateTime($dateDebutFilter) : null,
                $dateFinFilter ? new \DateTimeImmutable($dateFinFilter) : null
            );

            return $this->redirectToRoute('sortie_show', [
                'search' => $searchFilter,
                'campus' => $campusFilter,
                'dateDebut' => $dateDebutFilter,
                'dateFin' => $dateFinFilter
            ]);
        }

        $searchFilter = $request->query->get('search');
        $campusFilter = $request->query->get('campus');
        $dateDebutFilter = $request->query->get('dateDebut');
        $dateFinFilter = $request->query->get('dateFin');

        if ($searchFilter || $campusFilter || $dateDebutFilter || $dateFinFilter) {
            $sorties = $sortieRepository->findByFilters(
                $searchFilter,
                $campusFilter,
                $dateDebutFilter ? new \DateTime($dateDebutFilter) : null,
                $dateFinFilter ? new \DateTimeImmutable($dateFinFilter) : null
            );
        }

        if ($request->isMethod('POST')) {
            //Récupération des requests
            $campusFilter = $request->request->get('campus');
            $searchFilter = $request->request->get('search');
            $dateDebutFilter = $request->request->get('dateDebut');
            $dateFinFilter = $request->request->get('dateFin');

            //Conversion de campusID en entier ou de la chaine vide en null
            $campusFilter = ($campusFilter !== 'null' && $campusFilter !== '') ? (int)$campusFilter : null;

            $sorties = $sortieRepository->findByFilters(
                $searchFilter,
                $campusFilter,
                $dateDebutFilter ? new \DateTime($dateDebutFilter) : null,
                $dateFinFilter ? new \DateTimeImmutable($dateFinFilter) : null
            );
        }

        return $this->render('sortie/show.html.twig', [
            'sorties' => $sorties,
            'campus' => $campus,
            'campusUtilisateur' => $campusUtilisateur,
            'forms' => $forms,
        ]);
    }

    #[Route(path: "/{id}", name: 'sortie_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(Sortie $sortie): Response
    {

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/creation', name: 'sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {

        if($this->mobileDetect->isMobile()) {
            $this->addFlash("danger", "Cette fonctionnalité n'est pas disponible sur smartphone");
            return $this->redirectToRoute('sortie_show', []);
        }

        $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);

        $sortie = new Sortie();
        $sortie->setOrganisateur($this->getUser());
        $sortie->setEtat($etat);
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/modifier', name: 'sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $isModified = true;


        if($this->mobileDetect->isMobile()) {
            $this->addFlash("danger", "Cette fonctionnalité n'est pas disponible sur smartphone");
            return $this->redirectToRoute('sortie_show', []);
        }


        $form = $this->createForm(SortieType::class, $sortie, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $publier = $form->get('publier')->getData();
            if($publier){
                $sortie->setEtat($etat);
            }
            $entityManager->flush();

            return $this->redirectToRoute('sortie_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'isModified' => $isModified,
        ]);
    }

    #[Route('/{id}', name: 'sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if($this->mobileDetect->isMobile()) {
            $this->addFlash("danger", "Cette fonctionnalité n'est pas disponible sur smartphone");
            return $this->redirectToRoute('sortie_show', []);
        }

        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }
        return $this->redirectToRoute('sortie_show', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/inscription', name: 'sortie_inscription', methods: ['GET'])]
    public function inscription(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $user = $this->getUser();

        $nbInscriptionsMax = $sortie->getNbInscriptionsMax();
        $nbParticipantsInscrits = count($sortie->getParticipants());
        $dateLimiteInscription = $sortie->getDateLimiteInscription();
        $dateDuJour = new \DateTime();
        $listeParticipants = $sortie->getParticipants();
        $heureDebutSortie = $sortie->getDateHeureDebut();
        $duree = $sortie->getDuree();
        $heureFinSortie = (clone $heureDebutSortie)->modify('+' . $duree . ' minute');
        $listeSortie = $user->getSorties();
        $listeSortieJour = $listeSortie->contains($sortie);
        $etat = $sortie->getEtat()->getLibelle();
        $sortieValidee = true;

        if (
            $nbParticipantsInscrits < $nbInscriptionsMax &&
            $dateDuJour < $dateLimiteInscription &&
            !$listeParticipants->contains($user) &&
            $etat == "Ouverte" &&
            $user != $sortie->getOrganisateur()) {

            if ($listeSortieJour) {
                for ($i = 0; $i < $listeSortieJour; $i++) {
                    $heureDebutSortie2 = $listeSortieJour[$i]->getDateHeureDebut();
                    $duree2 = $listeSortieJour[$i]->getDuree();
                    $heureFinSortie2 = (clone $heureDebutSortie2)->modify('+' . $duree2 . ' minute');
                    if ($listeSortieJour[$i]->getStatut()->getLibellee() == "Ouverte"){
                        if ($heureFinSortie2 > $heureDebutSortie && $heureDebutSortie2 < $heureFinSortie) {
                            $sortieValidee = false;
                            $this->addFlash("danger", "Vous êtes déjà occupé à ce moment-là !");
                            break;
                        }
                    }
                }
            }

            if ($sortieValidee) {
                $sortie->addParticipant($user);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash("success", "Vous êtes bien inscrit à cette sortie.");
            }

        } else {

            $this->addFlash("danger", "Vous ne pouvez pas vous inscrire.");
            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('sortie_detail', [
            'id' => $sortie->getId(),
        ], Response::HTTP_SEE_OTHER);

    }

    #[Route('/{id}/desistement', name: 'sortie_desistement', methods: ['GET'])]
    public function desistement(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $dateDuJour = new \DateTime();
        $heureDebutSortie = $sortie->getDateHeureDebut();
        $listeParticipants = $sortie->getParticipants();

            if($listeParticipants->contains($user) &&
            $dateDuJour < $heureDebutSortie) {

                $sortie->removeParticipant($user);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash("success", "Vous êtes bien désinscrit.");
            }

            else {
                $this->addFlash("danger", "Vous ne pouvez pas vous désinscrire.");
            }
            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ], Response::HTTP_SEE_OTHER);

    }

    #[Route('/{id}/annuler', name: 'sortie_annuler', methods: ['GET', 'POST'])]
    public function annuler(Request $request, Sortie $sortie, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
        $organisateur = $sortie->getOrganisateur();
        $user = $this->getUser();
        $form = $this->createForm(AnnulationType::class);
        $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid()) {

        if($organisateur->getId() == $user->getId() || in_array("ROLE_ADMIN", $user->getRoles()) ) {
            $motif=$form->get('motifAnnulation')->getData();

            if($motif == 'Autre') {
                $autre=$form->get('autre')->getData();
                $sortie->setMotifAnnulation($autre);
            } else {
                $sortie->setMotifAnnulation($motif);
            }

            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash("success", "La sortie a bien été annulée.");
        } else {
            $this->addFlash("danger", "Vous ne pouvez annuler cette sortie.");
        }
        return $this->redirectToRoute('sortie_detail', [
            'id' => $sortie->getId(),
        ], Response::HTTP_SEE_OTHER);
       }
       return $this->render('sortie/annuler.html.twig', [
           'sortie' => $sortie,
           'form' => $form->createView(),]);
    }
}
