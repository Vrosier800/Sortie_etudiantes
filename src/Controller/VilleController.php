<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ville')]
final class VilleController extends AbstractController
{
    #[Route(name: 'ville_list', methods: ['GET'])]
    public function index(VilleRepository $villeRepository): Response
    {
        return $this->render('ville/index.html.twig', [
            'villes' => $villeRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/creer', name: 'ville_creer', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $url = 'https://geo.api.gouv.fr/communes?fields=nom,codePostal&limit=300';

        $response = file_get_contents($url);
        $communes = json_decode($response, true);

        $villes = [];
        foreach ($communes as $commune) {
            $nom = $commune['nom'];
            $codePostal = isset($commune['code']) ? $commune['code'] : 'N/A';

            $villes["$nom ($codePostal)"] = "$nom-$codePostal";
        }

        $form = $this->createForm(VilleType::class, null, [
            'villes' => $villes,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $villeString = $form->get('nom')->getData();
            if (preg_match('/^(.*?)-(\d{5})$/', $villeString, $matches)) {
                $nom = $matches[1];
                $codePostal = $matches[2];

                $ville = new Ville();
                $ville->setNom($nom);
                $ville->setCodePostal($codePostal);

                $entityManager->persist($ville);
                $entityManager->flush();

                $this->addFlash('succes', 'Ville créée avec succes');
                return $this->redirectToRoute('ville_list');
            } else {
                $this->addFlash('error', 'Le format de la ville est invalide.');
            }
        }

        return $this->render('ville/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'ville_detail', methods: ['GET'])]
    public function show(Ville $ville): Response
    {
        return $this->render('ville/show.html.twig', [
            'ville' => $ville,
        ]);
    }

    #[Route('/{id}/modifier', name: 'ville_modifier', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $url = 'https://geo.api.gouv.fr/communes?fields=nom,codePostal&limit=300';

        $response = file_get_contents($url);
        $communes = json_decode($response, true);

        $villes = [];
        foreach ($communes as $commune) {
            $nom = $commune['nom'];
            $codePostal = isset($commune['code']) ? $commune['code'] : 'N/A';

            $villes["$nom ($codePostal)"] = "$nom-$codePostal";
        }

        $form = $this->createForm(VilleType::class, null, [
            'villes' => $villes,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $villeString = $form->get('nom')->getData();

            if (preg_match('/^(.*?)-(\d{5})$/', $villeString, $matches)) {
                $nom = $matches[1];
                $codePostal = $matches[2];

                $ville = new Ville();
                $ville->setNom($nom);
                $ville->setCodePostal($codePostal);

                $entityManager->persist($ville);
                $entityManager->flush();

                $this->addFlash('succes', 'Ville créée avec succes');
                return $this->redirectToRoute('ville_list');
            } else {
                $this->addFlash('error', 'Le format de la ville est invalide.');
            }
        }

        return $this->render('ville/edit.html.twig', [
            'form' => $form->createView(),
            'ville' => $ville,
        ]);
    }

    #[Route('/{id}', name: 'ville_supprimer', methods: ['POST'])]
    public function delete(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ville->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ville);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ville_list', [], Response::HTTP_SEE_OTHER);
    }
}
