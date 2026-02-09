<?php

namespace App\Repository;

use App\Entity\Whiteboard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WhiteboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Whiteboard::class);
    }

    public function findPublicWhiteboards(): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.is_public = :public')
            ->setParameter('public', true)
            ->orderBy('w.updated_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByMeeting(int $meetingId): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.meeting = :meeting')
            ->setParameter('meeting', $meetingId)
            ->orderBy('w.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentWhiteboards(int $limit = 10): array
    {
        return $this->createQueryBuilder('w')
            ->orderBy('w.updated_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
