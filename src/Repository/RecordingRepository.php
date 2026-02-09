<?php

namespace App\Repository;

use App\Entity\Recording;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecordingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recording::class);
    }

    public function findByMeeting(int $meetingId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.meeting = :meetingId')
            ->setParameter('meetingId', $meetingId)
            ->orderBy('r.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCompleted(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.status = :status')
            ->setParameter('status', 'completed')
            ->orderBy('r.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
