<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Form\LieuAutocompleteField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
        $isSortieEdit = $options['is_edit'] ?? false;
        $form = $event->getForm();


        $form
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie :'
            ])
            ->add('dateHeureDebut', null, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
            ])
            ->add('dateLimiteInscription', null, [
                'label' => 'Date limite d\'inscription :',
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places :',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Duree (minutes) :'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos :'
            ])
            ->add('siteOrganisateur', EntityType::class, [
                'class' => Campus::class,
                'label' => 'Campus :',
                'choice_label' => 'nom',
            ])
            ->add('lieu', LieuAutocompleteField::class)
        ;
            if ($isSortieEdit) {
                $form->add('publier', CheckboxType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Publier la sortie',
                    'label_attr' => [
                        'class' => 'btn btn-outline-success',
                    ],
                    'attr' => [
                        'class' => 'btn-check',
                        'autocomplete' => 'off',
                        'id' => 'registration_form_actif',
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'is_edit' => false,
        ]);
    }
}
