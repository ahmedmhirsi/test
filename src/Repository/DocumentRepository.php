<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Recherche et tri des documents
     */
    public function findBySearchAndSort(
        ?string $search = null,
        ?string $status = null,
        ?string $sortField = 'uploadedAt',
        string $sortDirection = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.uploadedBy', 'u')
            ->addSelect('u');

        // Filtre par recherche (nom de fichier ou description)
        if ($search) {
            $qb->andWhere('d.originalName LIKE :search OR d.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par statut
        if ($status) {
            $qb->andWhere('d.status = :status')
               ->setParameter('status', $status);
        }

        // Tri
        $allowedSortFields = ['uploadedAt', 'originalName', 'size', 'status'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'uploadedAt';
        }

        $sortDirection = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';
        $qb->orderBy('d.' . $sortField, $sortDirection);

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques des documents
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('d');
        
        return [
            'total' => $qb->select('COUNT(d.id)')->getQuery()->getSingleScalarResult(),
            'pending' => $qb->select('COUNT(d.id)')
                ->where('d.status = :status')
                ->setParameter('status', 'pending')
                ->getQuery()->getSingleScalarResult(),
            'processed' => $qb->select('COUNT(d.id)')
                ->where('d.status = :status')
                ->setParameter('status', 'processed')
                ->getQuery()->getSingleScalarResult(),
            'error' => $qb->select('COUNT(d.id)')
                ->where('d.status = :status')
                ->setParameter('status', 'error')
                ->getQuery()->getSingleScalarResult(),
            'totalSize' => $qb->select('SUM(d.size)')
                ->getQuery()->getSingleScalarResult() ?? 0,
        ];
    }

    /**
     * Documents récents
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.uploadedBy', 'u')
            ->addSelect('u')
            ->orderBy('d.uploadedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
