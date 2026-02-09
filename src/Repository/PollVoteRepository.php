<?php

namespace App\Repository;

use App\Entity\PollVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PollVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollVote::class);
    }

    public function hasUserVoted(int $pollId, int $userId): bool
    {
        $count = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->join('v.option', 'o')
            ->join('o.poll', 'p')
            ->where('p.id = :pollId')
            ->andWhere('v.user = :userId')
            ->setParameter('pollId', $pollId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
