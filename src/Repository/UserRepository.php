<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Recherche et tri des utilisateurs
     * 
     * @return User[]
     */
    public function findBySearchAndSort(
        ?string $search = null,
        ?string $role = null,
        ?bool $isActive = null,
        string $sortBy = 'createdAt',
        string $sortOrder = 'DESC'
    ): array
    {
        $qb = $this->createQueryBuilder('u');

        // Recherche par nom, prénom ou email
        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par rôle
        if ($role) {
            $qb->andWhere('u.roles LIKE :role')
               ->setParameter('role', '%' . $role . '%');
        }

        // Filtre par statut actif/inactif
        if ($isActive !== null) {
            $qb->andWhere('u.isActive = :isActive')
               ->setParameter('isActive', $isActive);
        }

        // Tri (colonnes autorisées pour sécurité)
        $allowedSorts = ['nom', 'prenom', 'email', 'createdAt', 'lastLoginAt'];
        if (in_array($sortBy, $allowedSorts)) {
            $order = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
            $qb->orderBy('u.' . $sortBy, $order);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère uniquement les utilisateurs avec le rôle EMPLOYEE
     * Pour l'export PDF
     * 
     * @return User[]
     */
    public function findEmployees(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_EMPLOYEE%')
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
