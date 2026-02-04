<?php

namespace App\Repository;

use App\Entity\MarketingCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarketingCampaign>
 */
class MarketingCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketingCampaign::class);
    }

    /**
     * @return MarketingCampaign[] Returns all campaigns ordered by start date
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MarketingCampaign[] Returns active campaigns
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', MarketingCampaign::STATUS_ACTIVE)
            ->orderBy('c.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MarketingCampaign[] Returns campaigns by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', $status)
            ->orderBy('c.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get campaign statistics
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('c');
        
        $total = $qb->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $active = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', MarketingCampaign::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => (int) $total,
            'active' => (int) $active,
        ];
    }
}
