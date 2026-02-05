<?php

namespace App\Repository;

use App\Entity\OffreEmploi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OffreEmploi>
 */
class OffreEmploiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OffreEmploi::class);
    }

    /**
     * @return OffreEmploi[] Returns an array of OffreEmploi objects
     */
    public function searchByPoste(string $value): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.poste LIKE :val OR o.description LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('o.datePublication', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
