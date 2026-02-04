<?php

namespace App\Repository;

use App\Entity\MarketingLead;
use App\Entity\MarketingCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarketingLead>
 */
class MarketingLeadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketingLead::class);
    }

    /**
     * @return MarketingLead[] Returns all leads ordered by creation date
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MarketingLead[] Returns leads by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.status = :status')
            ->setParameter('status', $status)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MarketingLead[] Returns new leads (not yet contacted)
     */
    public function findNewLeads(): array
    {
        return $this->findByStatus(MarketingLead::STATUS_NEW);
    }

    /**
     * Get lead statistics
     */
    public function getStatistics(): array
    {
        $total = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $converted = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.status = :status')
            ->setParameter('status', MarketingLead::STATUS_CONVERTED)
            ->getQuery()
            ->getSingleScalarResult();

        $newLeads = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.status = :status')
            ->setParameter('status', MarketingLead::STATUS_NEW)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => (int) $total,
            'converted' => (int) $converted,
            'new' => (int) $newLeads,
            'conversionRate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Get leads grouped by status
     */
    public function getCountByStatus(): array
    {
        $results = $this->createQueryBuilder('l')
            ->select('l.status, COUNT(l.id) as count')
            ->groupBy('l.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['status']] = (int) $result['count'];
        }

        return $counts;
    }
}
