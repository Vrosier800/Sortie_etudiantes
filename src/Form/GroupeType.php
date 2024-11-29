<?php
namespace App\Form;

use App\Entity\Groupe;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();

// Utiliser la clé correcte 'isUpdate' au lieu de 'isUdate'
            $isUpdate = $options['isUpdate'] ?? false;

// Ajouter un champ 'nom' avec ou sans 'required' selon 'isUpdate'
            if ($isUpdate) {
                $form->add('nom', TextType::class, [
                    'label' => 'Nom du groupe',
                    'required' => false,  // champ non obligatoire lors de la mise à jour
                ]);
            } else {
                $form->add('nom', TextType::class, [
                    'label' => 'Nom du groupe',
                    'required' => true,  // champ obligatoire lors de la création
                ]);
            }

// Ajouter le champ 'utilisateurs' de type EntityType
            $form->add('utilisateurs', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'fullname',
                'multiple' => true,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Groupe::class,  // Lier ce formulaire à l'entité Groupe
            'isUpdate' => false,  // Définir une valeur par défaut pour 'isUpdate'
        ]);
    }
}