<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('motifAnnulation', ChoiceType::class, [
                'choices' => [
                    'Intempéries' => 'Intempéries',
                    'Grève' => 'Grève',
                    'Fin du monde' => 'Fin du monde',
                    'Flemme' => 'Flemme',
                    'Autre' => 'Autre', // option pour "Autre"

                ],
                'expanded' => false, // Afficher sous forme de select
                'placeholder' => 'Choisissez une option :', // Option par défaut
                'mapped' => false,
            ])
            ->add('autre', TextType::class, [
                'required' => false, // Pas obligatoire
                'label' => 'Autre (Précisez)',
                'mapped' => false,

            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
