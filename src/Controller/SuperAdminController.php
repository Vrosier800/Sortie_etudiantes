<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Notification\SenderMail;
use App\Util\RegisterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SuperAdminController extends AbstractController
{
    private RegisterService $registerService;

    public function __construct(RegisterService $registerService){
        $this->registerService = $registerService;
    }

    #[Route(path: '/gestion/admin/{id}', name: 'superadmin_ajoutAdmin', methods: ['GET', 'POST'])]
    public function ajoutAdmin(Request $request, Participant $participant, EntityManagerInterface $entityManager, SenderMail $sender): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $participant, ['is_profile_edit' => true]);
        $form->handleRequest($request);

        $isNew = false;
        $isSuperAdmin = true;

        if($request->getMethod() == 'POST') {
            if($form->isSubmitted() && $form->isValid()){

                $password = $form->get('plainPassword')->getData();
                $uploadFile = $form->get('file')->getData();
                $isAdmin = $form->get('admin')->getData();

                if($isAdmin){
                    $participant->setAdministrateur(true);
                } else {
                    $participant->setAdministrateur(false);
                }

                $this->registerService->process($participant, $password, $uploadFile, false, $sender);

                $this->addFlash("success", "Le participant a été modifié avec succès");
                return $this->redirectToRoute('admin_listParticipant');
            } else if ($form->isSubmitted() && !$form->isValid()){
                $this->addFlash("danger", "Une erreur est survenue");
                return $this->redirectToRoute('admin_listParticipant');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            "isNew" => $isNew,
        ]);
    }
}
