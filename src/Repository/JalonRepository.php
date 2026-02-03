<?php

namespace App\Repository;

use App\Entity\Jalon;
use App\Entity\Projet;
use App\Entity\Sprint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Jalon>
 */
class JalonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Jalon::class);
    }

    /**
     * Find all milestones for a project
     * @return Jalon[]
     */
    public function findByProjet(Projet $projet): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.projet = :projet')
            ->setParameter('projet', $projet)
            ->orderBy('j.dateEcheance', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find upcoming milestones (not yet reached, ordered by deadline)
     * @return Jalon[]
     */
    public function findUpcoming(int $limit = 5): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.statut != :statut')
            ->andWhere('j.dateEcheance >= :today')
            ->setParameter('statut', 'atteint')
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('j.dateEcheance', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all milestones for a sprint
     * @return Jalon[]
     */
    public function findBySprint(Sprint $sprint): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.sprint = :sprint')
            ->setParameter('sprint', $sprint)
            ->orderBy('j.dateEcheance', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find overdue milestones
     * @return Jalon[]
     */
    public function findOverdue(): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.statut != :statut')
            ->andWhere('j.dateEcheance < :today')
            ->setParameter('statut', 'atteint')
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('j.dateEcheance', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
