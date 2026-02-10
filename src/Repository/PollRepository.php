<?php

namespace App\Repository;

use App\Entity\Poll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Poll>
 */
class PollRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poll::class);
    }

    /**
     * Find all active polls
     * @return Poll[] Returns an array of Poll objects
     */
    public function findActivePolls(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 'Active')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find polls by meeting
     * @return Poll[] Returns an array of Poll objects
     */
    public function findByMeeting(int $meetingId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.meeting = :meeting')
            ->setParameter('meeting', $meetingId)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
