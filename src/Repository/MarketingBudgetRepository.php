<?php

namespace App\Repository;

use App\Entity\MarketingBudget;
use App\Entity\MarketingCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarketingBudget>
 */
class MarketingBudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketingBudget::class);
    }

    /**
     * Get total budgets by campaign
     */
    public function getTotalsByCampaign(MarketingCampaign $campaign): array
    {
        $result = $this->createQueryBuilder('b')
            ->select('SUM(b.plannedAmount) as totalPlanned, SUM(b.actualAmount) as totalActual')
            ->where('b.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getSingleResult();

        return [
            'planned' => (float) ($result['totalPlanned'] ?? 0),
            'actual' => (float) ($result['totalActual'] ?? 0),
        ];
    }

    /**
     * Get overall budget statistics
     */
    public function getOverallStats(): array
    {
        $result = $this->createQueryBuilder('b')
            ->select('SUM(b.plannedAmount) as totalPlanned, SUM(b.actualAmount) as totalActual')
            ->getQuery()
            ->getSingleResult();

        return [
            'totalPlanned' => (float) ($result['totalPlanned'] ?? 0),
            'totalActual' => (float) ($result['totalActual'] ?? 0),
        ];
    }
}
