<?php

namespace App\Repository;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Find role by name
     */
    public function findByName(string $name): ?Role
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Find all custom (non-system) roles
     */
    public function findCustomRoles(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.is_system = :isSystem')
            ->setParameter('isSystem', false)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all system roles
     */
    public function findSystemRoles(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.is_system = :isSystem')
            ->setParameter('isSystem', true)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find roles for a specific user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.users', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get or create role
     */
    public function getOrCreate(string $name, ?string $description = null, bool $isSystem = false): Role
    {
        $role = $this->findByName($name);
        
        if (!$role) {
            $role = new Role();
            $role->setName($name);
            $role->setDescription($description);
            $role->setIsSystem($isSystem);
            
            $this->getEntityManager()->persist($role);
            $this->getEntityManager()->flush();
        }
        
        return $role;
    }

    /**
     * Count users with a specific role
     */
    public function countUsersWithRole(Role $role): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(u.id)')
            ->innerJoin('r.users', 'u')
            ->where('r.id = :roleId')
            ->setParameter('roleId', $role->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
