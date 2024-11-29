<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $isProfileEdit = $options['is_profile_edit'] ?? false;
            $user = $event->getData();
            $form = $event->getForm();

            $form->add('admin', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Administrateur',
                'label_attr' => [
                    'class' => 'btn btn-outline-success',
                ],
                'attr' => [
                    'class' => 'btn-check',
                    'autocomplete' => 'true',
                    'id' => 'registration_form_actif',
                ]
            ]);

            if ($isProfileEdit && $user && $user->getFilename()) {
                $form->add('deleteCb', CheckboxType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label'=> false,
                ]);
            }

            if (!$isProfileEdit) {
                $form->add('actif', CheckboxType::class, [
                    'required' => false,
                    'mapped' => false,
                    'label' => false,
                ]);
            }

            if ($isProfileEdit) {
                $form->add('file', FileType::class, [
                        'label' => 'Image',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new Image([
                                'maxSize' => '6000k',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                ],
                                'mimeTypesMessage' => 'Please upload a jpg or png image',
                            ]),
                        ]
                    ]);
            }

            $form->add('mail', TextType::class, [
                'label' => 'Email',
            ])
                ->add('nom', TextType::class, [
                    'label' => 'Nom'
                ])
                ->add('prenom', TextType::class, [
                    'label' => 'Prénom'
                ])
                ->add('pseudo', TextType::class, [
                    'label' => 'Pseudo',
                    'required' => false,
                ])
                ->add('telephone', TextType::class, [
                    'label' => 'Téléphone',
                    'required' => false,
                ])
            ;

            if (!$isProfileEdit) {
                $form->add("campus", EntityType::class, [
                    "label" => "Campus",
                    'class' => Campus::class,
                    "choice_label" => "nom",
                    "placeholder" => "Choisissez un campus",
                    ])
                ;
            } else {

                $form->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options'  => ['label' => 'Nouveau mot de passe (optionnel)'],
                    'second_options' => ['label' => 'Si changement, confirmer votre mot de passe'],
                    'mapped' => false,
                    'required' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                            'max' => 4096,
                        ]),
                    ],
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'is_profile_edit' => false,
        ]);
    }
}
