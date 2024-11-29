<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Util\RegisterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private RegisterService $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    #[Route('/modifier/{id}', name: 'app_modifier')]
    public function update(Request $request, Participant $user, ?UploadedFile $uploadedFile): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user, ['is_profile_edit' => true]);
        $form->handleRequest($request);

        if($request->isMethod('POST')) {

            if ($form->isSubmitted() && $form->isValid()) {

                $uploadedFile = $form->get('file')->getData();

                if ($form->has('deleteCb')) {
                    // Vérifiez si la case est cochée (getData() renvoie true ou false)
                    $deleteCb = $form->get('deleteCb')->getData();

                    if ($deleteCb) {
                        // Vérifiez si le fichier existe
                        $filePath = 'uploads/img_profile/' . $user->getFilename();

                        if (file_exists($filePath)) {
                            // Si le fichier existe, on le supprime
                            unlink($filePath);

                            // Ensuite, on met à jour la propriété du fichier à null
                            $user->setFilename(null);
                        }
                    }
                }

                $plainPassword = $form->get('plainPassword')->getData();

                $this->registerService->process($user, $plainPassword, $uploadedFile, false);


                $this->addFlash('success', 'Profil mis à jour avec succès !');
            } else if($form->isSubmitted() && !$form->isValid()) {

                $this->addFlash('danger', 'Une erreur est survenue !');

            }
                return $this->redirectToRoute('main_home');

    }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'isNew' => false,
        ]);
    }


}