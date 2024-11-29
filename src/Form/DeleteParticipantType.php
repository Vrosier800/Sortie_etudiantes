<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer les participants dans les options
        $participants = $options['participants'];

        // Ajouter un champ checkbox pour chaque participant
        foreach ($participants as $index => $participant) {
            $builder->add('delete_' . $index, CheckboxType::class, [
                'label' => false,
                'required' => false,
            ]);
        }

        // Bouton de soumission
        $builder->add('submit', SubmitType::class, [
            'label' => 'Supprimer',
        ]);
    }
    // Le formulaire attend un tableau de participants
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'participants' => [],
        ]);
    }
}