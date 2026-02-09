<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Find unread notifications for a user
     */
    public function findUnreadByUser(int $userId): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.statut = :statut')
            ->setParameter('user', $userId)
            ->setParameter('statut', 'NonLu')
            ->orderBy('n.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unread notifications for a user
     */
    public function countUnreadByUser(int $userId): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.user = :user')
            ->andWhere('n.statut = :statut')
            ->setParameter('user', $userId)
            ->setParameter('statut', 'NonLu')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find all notifications for a user
     */
    public function findByUser(int $userId, int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->setParameter('user', $userId)
            ->orderBy('n.date_creation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
