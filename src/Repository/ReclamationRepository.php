<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    /**
     * Find all reclamations ordered by creation date (newest first)
     *
     * @return Reclamation[]
     */
    public function findAllOrderedByDateDesc(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reclamations by status
     *
     * @param string $statut
     * @return Reclamation[]
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count reclamations with status 'en_cours' (unanswered)
     *
     * @return int
     */
    public function countEnCours(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.statut = :statut')
            ->setParameter('statut', 'en_cours')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find recent reclamations with status 'en_cours'
     *
     * @param int $limit
     * @return Reclamation[]
     */
    public function findRecentEnCours(int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.statut = :statut')
            ->setParameter('statut', 'en_cours')
            ->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


    /**
     * Find reclamations with filters
     *
     * @param array $filters
     * @return Reclamation[]
     */
    public function findWithFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('r');

        if (isset($filters['statut']) && $filters['statut']) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $filters['statut']);
        }



        return $qb->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reclamations not deleted by client (for front-office)
     *
     * @return Reclamation[]
     */
    public function findAllNotDeletedByClient(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.deletedByClient = :deleted')
            ->setParameter('deleted', false)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search and sort reclamations (for back-office)
     *
     * @param int|null $id
     * @param string|null $email
     * @param string $sortBy
     * @param string $sortOrder
     * @return Reclamation[]
     */
    public function searchAndSort(
        ?int $id,
        ?string $email,
        string $sortBy = 'dateCreation',
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('r');

        // Filtrer par ID si fourni
        if ($id !== null) {
            $qb->andWhere('r.id = :id')
                ->setParameter('id', $id);
        }

        // Filtrer par email si fourni (recherche partielle)
        if ($email !== null && $email !== '') {
            $qb->andWhere('r.email LIKE :email')
                ->setParameter('email', '%' . $email . '%');
        }

        // Valider les colonnes de tri pour éviter l'injection SQL
        $allowedSort = ['dateCreation', 'statut', 'id', 'titre'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'dateCreation';
        }

        // Valider l'ordre de tri
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        return $qb->orderBy('r.' . $sortBy, $sortOrder)
            ->getQuery()
            ->getResult();
    }
}
