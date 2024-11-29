<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFilters(
        ?string $search = null,
        ?int $campusId = null,
        ?\DateTime $dateDebut = null,
        ?\DateTimeImmutable $dateFin = null
    ) {
        $qb = $this->createQueryBuilder('s');

        if ($search) {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($campusId !== null) {
            $qb->innerJoin('s.siteOrganisateur', 'c')
            ->andWhere('c.id = :campusId')
            ->setParameter('campusId', $campusId);
        }

        if ($dateDebut) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut->format('Y-m-d H:i:s'));
        }

        if ($dateFin) {
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $dateFin->format('Y-m-d H:i:s'));
        }

        // Tri par nom
        $qb->orderBy('s.nom', 'ASC');
        return $qb->getQuery()->getResult();
    }


//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
