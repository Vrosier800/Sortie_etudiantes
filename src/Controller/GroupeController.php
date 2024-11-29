<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Form\GroupeDeleteType;
use App\Form\GroupeType;
use App\Repository\GroupeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/groupe')]
class GroupeController extends AbstractController
{
    #[Route('/creer', name: 'groupe_creer', methods: ['GET', 'POST'])]
    public function creer(Request $request, EntityManagerInterface $em): Response
    {
        $groupe = new Groupe();
        $form = $this->createForm(GroupeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $utilisateurs = $form->get('utilisateurs')->getData();
            $nom = $form->get('nom')->getData();

            $groupe->setProprietaire($user);
            foreach ($utilisateurs as $utilisateur) {
                $groupe->addUtilisateur($utilisateur);
            }

            $groupe->setNom($nom);

            $em->persist($groupe);
            $em->flush();
            $this->addFlash('success', "Groupe bien créé");
            return $this->redirectToRoute('groupe_list');
        }
        return $this->render('groupe/creer_groupe.html.twig', [
            'form' => $form->createView(),
            'isUpdate' => false,
        ]);
    }
    #[Route('/{id}/modifier', name: 'groupe_modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $request, EntityManagerInterface $em, Groupe $groupe): Response
    {
        $isUpdate=true;
        $form = $this->createForm(GroupeType::class, $groupe, [
            'isUpdate' => $isUpdate,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $utilisateurs = $form->get('utilisateurs')->getData();
            $nom = $form->get('nom')->getData();

            $groupe->setProprietaire($user);
            foreach ($utilisateurs as $utilisateur) {
                $groupe->addUtilisateur($utilisateur);
            }

            $groupe->setNom($nom);

            $em->persist($groupe);
            $em->flush();
            $this->addFlash('success', "Groupe bien modifié");
            return $this->redirectToRoute('groupe_list');
        }
        return $this->render('groupe/creer_groupe.html.twig', [
            'controller_name' => 'GroupeController',
            'form' => $form->createView(),
            'isUpdate' => $isUpdate,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'groupe_supprimer', methods: ['GET', 'POST'])]
    public function supprimer(Request $request, EntityManagerInterface $em, Groupe $groupe): Response
    {
        $user = $this->getUser();
        if ($user->getId() == $groupe->getProprietaire()->getId()) {

            $em->remove($groupe);
            $em->flush();

            $this->addFlash('success', "Groupe bien supprimé !");
            return $this->redirectToRoute('groupe_list');
        } else {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé à supprimer ce groupe');
            return $this->redirectToRoute('groupe_list');
        }

        $this->addFlash('danger', 'Problème lors de la suppression du groupe');
        return $this->redirectToRoute('groupe_list');
    }


    #[Route('/', name: 'groupe_list', methods: ['GET'])]
    public function list(GroupeRepository $groupeRepository): Response
    {
        $form = $this->createForm(GroupeDeleteType::class);
        $groupes = $groupeRepository->findBy([], ['nom' => 'DESC']);
        $groupesPrivee = [];
        foreach ($groupes as $groupe) {
            if ($groupe->getProprietaire()->getId() == $this->getUser()->getId()) {
                $groupesPrivee[] = $groupe;
            }
        }
        return $this->render('groupe/list_groupe.html.twig', [
            'groupes' => $groupesPrivee,
            'form' => $form->createView(),
        ]);
    }


}
