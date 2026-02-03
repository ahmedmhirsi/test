<?php

namespace App\Repository;

use App\Entity\JournalTemps;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JournalTemps>
 */
class JournalTempsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JournalTemps::class);
    }

    /**
     * Find all time entries for a specific user
     */
    public function findByUserId(int $userId, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('j')
            ->andWhere('j.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('j.date', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find time entries for a user within a date range
     */
    public function findByUserIdAndDateRange(int $userId, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.userId = :userId')
            ->andWhere('j.date >= :startDate')
            ->andWhere('j.date <= :endDate')
            ->setParameter('userId', $userId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('j.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total duration for a user in a date range (in minutes)
     */
    public function getTotalDurationByUserIdAndDateRange(int $userId, \DateTime $startDate, \DateTime $endDate): int
    {
        $result = $this->createQueryBuilder('j')
            ->select('SUM(j.duree) as total')
            ->andWhere('j.userId = :userId')
            ->andWhere('j.date >= :startDate')
            ->andWhere('j.date <= :endDate')
            ->setParameter('userId', $userId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Get total duration for a task (in minutes)
     */
    public function getTotalDurationByTask(int $tacheId): int
    {
        $result = $this->createQueryBuilder('j')
            ->select('SUM(j.duree) as total')
            ->andWhere('j.tache = :tacheId')
            ->setParameter('tacheId', $tacheId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Get weekly summary for a user (grouped by day)
     */
    public function getWeeklySummaryByUserId(int $userId, \DateTime $weekStart): array
    {
        $weekEnd = (clone $weekStart)->modify('+6 days');

        return $this->createQueryBuilder('j')
            ->select('j.date, SUM(j.duree) as totalDuree')
            ->andWhere('j.userId = :userId')
            ->andWhere('j.date >= :weekStart')
            ->andWhere('j.date <= :weekEnd')
            ->setParameter('userId', $userId)
            ->setParameter('weekStart', $weekStart)
            ->setParameter('weekEnd', $weekEnd)
            ->groupBy('j.date')
            ->orderBy('j.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent entries across all users (for admin dashboard)
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('j')
            ->orderBy('j.date', 'DESC')
            ->addOrderBy('j.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
