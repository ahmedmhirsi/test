<?php

namespace App\Repository;

use App\Entity\Channel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Channel>
 */
class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    /**
     * Find active channels
     */
    public function findActiveChannels(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', 'Actif')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find channels by type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.type = :type')
            ->setParameter('type', $type)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find most active channels (by message count)
     */
    /**
     * Find most active channels (by message count)
     */
    public function findMostActiveChannels(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->groupBy('c.id')
            ->orderBy('COUNT(m.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find channels with search and sort
     */
    public function findBySearchAndSort(?string $search, ?string $sort, string $direction = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.nom LIKE :search OR c.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($sort) {
            // Prevent SQL injection by allowing only specific fields
            $allowedSorts = ['nom', 'type', 'statut', 'max_participants'];
            if (in_array($sort, $allowedSorts)) {
                $qb->orderBy('c.' . $sort, $direction);
            }
        } else {
            $qb->orderBy('c.nom', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}
