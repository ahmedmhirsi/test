<?php

namespace App\Repository;

use App\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Permission>
 */
class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    /**
     * Find permission by name
     */
    public function findByName(string $name): ?Permission
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Find all permissions for a specific resource
     */
    public function findByResource(string $resource): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.resource = :resource')
            ->setParameter('resource', $resource)
            ->orderBy('p.action', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all permissions grouped by resource
     */
    public function findAllGroupedByResource(): array
    {
        $permissions = $this->createQueryBuilder('p')
            ->orderBy('p.resource', 'ASC')
            ->addOrderBy('p.action', 'ASC')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach ($permissions as $permission) {
            $resource = $permission->getResource();
            if (!isset($grouped[$resource])) {
                $grouped[$resource] = [];
            }
            $grouped[$resource][] = $permission;
        }

        return $grouped;
    }

    /**
     * Find permissions by resource and action
     */
    public function findByResourceAndAction(string $resource, string $action): ?Permission
    {
        return $this->findOneBy([
            'resource' => $resource,
            'action' => $action
        ]);
    }

    /**
     * Get or create permission
     */
    public function getOrCreate(string $name, string $resource, string $action, ?string $description = null): Permission
    {
        $permission = $this->findByName($name);
        
        if (!$permission) {
            $permission = new Permission();
            $permission->setName($name);
            $permission->setResource($resource);
            $permission->setAction($action);
            $permission->setDescription($description);
            
            $this->getEntityManager()->persist($permission);
            $this->getEntityManager()->flush();
        }
        
        return $permission;
    }
}
