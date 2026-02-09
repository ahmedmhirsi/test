<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Find visible messages
     */
    public function findVisibleMessages(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.statut = :statut')
            ->setParameter('statut', 'Visible')
            ->orderBy('m.date_envoi', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find messages by channel
     */
    public function findByChannel(int $channelId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.channel = :channel')
            ->andWhere('m.statut = :statut')
            ->setParameter('channel', $channelId)
            ->setParameter('statut', 'Visible')
            ->orderBy('m.date_envoi', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find messages containing specific hashtag
     */
    public function findByHashtag(string $hashtag): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.contenu LIKE :hashtag')
            ->andWhere('m.statut = :statut')
            ->setParameter('hashtag', '%#' . $hashtag . '%')
            ->setParameter('statut', 'Visible')
            ->orderBy('m.date_envoi', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find messages with search and sort
     */
    public function findBySearchAndSort(?string $search, ?string $sort, string $direction = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.channel', 'c');

        if ($search) {
            $qb->andWhere('m.contenu LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($sort) {
            $allowedSorts = ['date_envoi', 'contenu', 'statut'];
            if (in_array($sort, $allowedSorts)) {
                $qb->orderBy('m.' . $sort, $direction);
            } elseif ($sort === 'channel') {
                $qb->orderBy('c.nom', $direction);
            }
        } else {
            $qb->orderBy('m.date_envoi', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get top contributors based on message count
     */
    public function getTopContributors(int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.authorName as name', 'COUNT(m.id) as message_count')
            ->andWhere('m.statut = :statut')
            ->andWhere('m.type = :type')
            ->setParameter('statut', 'Visible')
            ->setParameter('type', 'user')
            ->groupBy('m.authorName')
            ->orderBy('message_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
