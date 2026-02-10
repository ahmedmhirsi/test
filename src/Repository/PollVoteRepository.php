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

    public function hasIpVoted(int $pollId, string $ipAddress): bool
    {
        $count = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->join('v.option', 'o')
            ->join('o.poll', 'p')
            ->where('p.id = :pollId')
            ->andWhere('v.ip_address = :ipAddress')
            ->setParameter('pollId', $pollId)
            ->setParameter('ipAddress', $ipAddress)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
