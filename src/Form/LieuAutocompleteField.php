<?php

namespace App\Form;


use App\Entity\Lieu;
use App\Repository\LieuRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class LieuAutocompleteField extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Lieu::class,
            'searchable_fields' => ['ville.nom'],
            'query_builder' => function(LieuRepository $repository) {

                $searchTerm = $_GET['q'] ?? '';

                return $repository->createQueryBuilder('l')
                    ->innerJoin('l.ville', 'v')
                    ->where('LOWER(v.nom) LIKE LOWER(:searchTerm)')
                    ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%')
                    ->orderBy('l.nom', 'ASC');
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}