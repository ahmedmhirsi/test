<?php

namespace App\Repository;

use App\Entity\MarketingPerformance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarketingPerformance>
 */
class MarketingPerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketingPerformance::class);
    }

    /**
     * Get overall performance metrics
     */
    public function getOverallMetrics(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('
                SUM(p.totalSpent) as totalSpent,
                SUM(p.totalLeads) as totalLeads,
                SUM(p.totalConverted) as totalConverted,
                AVG(p.cac) as avgCac,
                AVG(p.roi) as avgRoi
            ')
            ->getQuery()
            ->getSingleResult();

        $totalSpent = (float) ($result['totalSpent'] ?? 0);
        $totalConverted = (int) ($result['totalConverted'] ?? 0);
        
        return [
            'totalSpent' => $totalSpent,
            'totalLeads' => (int) ($result['totalLeads'] ?? 0),
            'totalConverted' => $totalConverted,
            'avgCac' => $totalConverted > 0 ? $totalSpent / $totalConverted : 0,
            'avgRoi' => (float) ($result['avgRoi'] ?? 0),
        ];
    }

    /**
     * Get performance by channel
     */
    public function getPerformanceByChannel(): array
    {
        return $this->createQueryBuilder('p')
            ->select('
                c.name as channelName,
                SUM(p.totalSpent) as spent,
                SUM(p.totalLeads) as leads,
                SUM(p.totalConverted) as converted
            ')
            ->leftJoin('p.channel', 'c')
            ->groupBy('c.id')
            ->orderBy('leads', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
