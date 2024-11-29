<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Form\CampusType;

use App\Form\DeleteParticipantType;
use App\Form\DisabledPartcipantType;
use App\Notification\SenderMail;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Util\SortieService;

use App\Form\ImportCsvType;
use App\Form\RegistrationFormType;
use App\Repository\CampusRepository;
use App\Util\AdminCsv;

use Doctrine\ORM\EntityManagerInterface;
use App\Util\RegisterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use function PHPUnit\Framework\isEmpty;
use function Webmozart\Assert\Tests\StaticAnalysis\uuid;


#[Route('/admin')]
class AdminController extends AbstractController
{
    private RegisterService $registerService;
    private SortieService $sortieService;
    private UserPasswordHasherInterface $hasher;


    public function __construct(
        RegisterService $registerService,
        SortieService $sortieService,
        AdminCsv $adminCsv,
        UserPasswordHasherInterface $hasher)
    {
        $this->registerService = $registerService;
        $this->sortieService = $sortieService;
        $this->adminCsv = $adminCsv;
        $this->hasher = $hasher;
    }

    #[Route(name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(): Response{
        return $this->render('admin/dashboard.html.twig');

    }

    #[Route(path: '/campus', name: 'admin_campus', methods: ['GET'])]
    public function campus(Request $request, CampusRepository $campusRepository): Response{

        $campus = $campusRepository->findBy([], ['nom' => 'ASC']);

        return $this->render('admin/campus.html.twig', [
            'campus' => $campus
        ]);
    }

    #[Route(path: '/participant', name: 'admin_listParticipant', methods: ['GET', 'POST'])]
    public function listParticipant
    (
        ParticipantRepository $participantRepository,
        EtatRepository $etatRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response

    {
        $etat = $etatRepository->findOneBy(['libelle' => 'Archivée']);

        $participants = $participantRepository->findBy([], ['nom' => 'ASC']);

        $form = $this->createForm(DisabledPartcipantType::class, null, [
            'participants' => $participants,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($participants as $index => $participant) {
                $checkbox = $form->get('participant_' . $index);
                if ($checkbox->getData()) {
                    if($participant->isActif()){
                        $this->sortieService->supprimerInscritionSorties($participant);
                        $this->sortieService->archiveSortieOrganisee($participant, $etat);
                        $participant->setActif(false);
                    }else{
                        $participant->setActif(true);
                    }
                    $em->persist($participant);
                }
            }
            foreach ($participants as $index => $participant) {
                $checkbox = $form->get('delete_' . $index);
                if ($checkbox->getData()) {
                        $this->sortieService->supprimerInscritionSorties($participant);
                        $this->sortieService->archiveSortieOrganisee($participant, $etat);
                        $participant->setActif(false);
                        $participant->setNom("Anonyme");
                        $participant->setPrenom("Anonyme");
                        $participant->setTelephone("00 00 00 00 00");
                        $em->persist($participant);
                    }
            }
            $em->flush();
            return $this->redirectToRoute('admin_listParticipant');
        }

        return $this->render('admin/listParticipant.html.twig', [
            'participants' => $participants,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/campus/modifier/{id}', name: 'admin_updateCampus', methods: ['GET', 'POST'])]
    public function updateCampus(Request $request, Campus $campus, EntityManagerInterface $entityManager): Response {
        $formCampus = $this->createForm(CampusType::class, $campus);
        $formCampus->handleRequest($request);
        $update = true;

        if($request->getMethod() == 'POST'){
            if($formCampus->isSubmitted() && $formCampus->isValid()){
                $entityManager->persist($campus);
                $entityManager->flush();
                $this->addFlash('success', 'Le nom du campus a bien été modifié');
            } elseif($formCampus->isSubmitted() && !$formCampus->isValid()){
                $this->addFlash('danger', 'Une erreur est survenue');
            }

            return $this->redirectToRoute('admin_campus');
        } else {
            return $this->render('form/CampusForm.html.twig', [
                'campus' => $formCampus->createView(),
                'update' => $update
            ]);
        }
    }


    #[Route(path: '/campus/creer', name: 'admin_createCampus', methods: ['GET', 'POST'])]
        public function createCampus(Request $request, EntityManagerInterface $entityManager): Response {

        $campus = new Campus();
        $formCampus = $this->createForm(CampusType::class, $campus);
        $formCampus->handleRequest($request);
        $update = false;


        if($request->getMethod() == 'POST'){
            if($formCampus->isSubmitted() && $formCampus->isValid()){
                $entityManager->persist($campus);
                $entityManager->flush();
                $this->addFlash('success', 'Le nom du campus a bien été créé');
            } elseif($formCampus->isSubmitted() && !$formCampus->isValid()){
                $this->addFlash('danger', 'Une erreur est survenue');
            }
            return $this->redirectToRoute('admin_campus');

        } else {
            return $this->render('form/CampusForm.html.twig', [
                'campus' => $formCampus->createView(),
                'update' => $update
            ]);
        }
    }

    #[Route(path: 'campus/supprimer/{id}', name: 'admin_deleteCampus', methods: ['POST'])]
    public function deleteCampus(Request $request, CampusRepository $campusRepository, Campus $campus, EntityManagerInterface $entityManager): Response{

        if($campus->getParticipants()->isEmpty() ||
            $campus->getSorties()->isEmpty()
        ){
            $entityManager->remove($campus);
            $entityManager->flush();
            $this->addFlash("success", "Le campus a bien été supprimé");
        } else {
            $this->addFlash("danger", "Vous ne pouvez pas supprimer un campus qui a des participants ou des sorties");
        }

        return $this->redirectToRoute('admin_campus', []);
    }

    #[Route(path: '/creer/participant', name: 'admin_createParticipant', methods:['GET', 'POST'])]
    public function createParticipant(Request $request, Security $security, SenderMail $sender): Response
    {
        $user = new Participant();


        $form = $this->createForm(RegistrationFormType::class, $user, ['is_profile_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $finalPassword = $user->getNom() . $user->getPrenom();

            $isActif = $form->get('actif')->getData();

            if($isActif){
                $user->setActif(true);
            }

            $this->registerService->process($user, $finalPassword, null, true, $sender);

            $this->addFlash('success', 'Compte créé avec succès !');

            return $this->redirectToRoute('main_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'isNew' => true,
        ]);
    }

    #[Route(path: '/importer', name: 'admin_importCsv', methods: ['GET', 'POST'])]
    public function importCsv(Request $request, EntityManagerInterface $entityManager): Response {
        $form = $this->createForm(ImportCsvType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $fichierCsv = $form->get('fichierCsv')->getData();

            if ($fichierCsv) {
                $chemin = $this->getParameter('kernel.project_dir') . '/public/uploads/importationcsv/';
                $nomFichier = uniqid() . '-' . $fichierCsv->getClientOriginalName();
                $fichierCsv->move($chemin, $nomFichier);

                $cheminComplet = $chemin . $nomFichier;

                    $erreurs = $this->adminCsv->importationFichier($cheminComplet);

                    if (!empty($erreurs)) {
                        foreach ($erreurs as $erreur) {
                            $this->addFlash('danger', $erreur);
                        }
                    } else {
                        $this->addFlash('success', 'Les utilisateurs ont bien été importés.');
                    }

                return $this->redirectToRoute('admin_importCsv');
            }
        }

        return $this->render('form/importCsv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
