<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPermission;
use App\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPermission>
 */
class UserPermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPermission::class);
    }

    /**
     * Find all permissions for a user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('up')
            ->where('up.user = :user')
            ->setParameter('user', $user)
            ->orderBy('up.granted_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find user permissions for a specific resource type
     */
    public function findByUserAndResourceType(User $user, ?string $resourceType = null): array
    {
        $qb = $this->createQueryBuilder('up')
            ->where('up.user = :user')
            ->setParameter('user', $user);

        if ($resourceType !== null) {
            $qb->andWhere('up.resource_type = :resourceType OR up.resource_type IS NULL')
                ->setParameter('resourceType', $resourceType);
        }

        return $qb->orderBy('up.granted_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find user permission for specific resource
     */
    public function findByUserAndResource(User $user, string $resourceType, int $resourceId): ?UserPermission
    {
        return $this->createQueryBuilder('up')
            ->where('up.user = :user')
            ->andWhere('up.resource_type = :resourceType')
            ->andWhere('up.resource_id = :resourceId')
            ->setParameter('user', $user)
            ->setParameter('resourceType', $resourceType)
            ->setParameter('resourceId', $resourceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find specific user permission
     */
    public function findUserPermission(
        User $user, 
        Permission $permission, 
        ?string $resourceType = null, 
        ?int $resourceId = null
    ): ?UserPermission {
        $qb = $this->createQueryBuilder('up')
            ->where('up.user = :user')
            ->andWhere('up.permission = :permission')
            ->setParameter('user', $user)
            ->setParameter('permission', $permission);

        if ($resourceType !== null) {
            $qb->andWhere('up.resource_type = :resourceType')
                ->setParameter('resourceType', $resourceType);
        } else {
            $qb->andWhere('up.resource_type IS NULL');
        }

        if ($resourceId !== null) {
            $qb->andWhere('up.resource_id = :resourceId')
                ->setParameter('resourceId', $resourceId);
        } else {
            $qb->andWhere('up.resource_id IS NULL');
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Check if user has specific permission for resource
     */
    public function hasPermission(
        User $user, 
        Permission $permission, 
        ?string $resourceType = null, 
        ?int $resourceId = null
    ): bool {
        $userPermission = $this->findUserPermission($user, $permission, $resourceType, $resourceId);
        return $userPermission !== null && $userPermission->isGranted();
    }

    /**
     * Grant permission to user
     */
    public function grantPermission(
        User $user, 
        Permission $permission, 
        ?User $grantedBy = null,
        ?string $resourceType = null, 
        ?int $resourceId = null
    ): UserPermission {
        $userPermission = $this->findUserPermission($user, $permission, $resourceType, $resourceId);
        
        if (!$userPermission) {
            $userPermission = new UserPermission();
            $userPermission->setUser($user);
            $userPermission->setPermission($permission);
            $userPermission->setResourceType($resourceType);
            $userPermission->setResourceId($resourceId);
            $userPermission->setGrantedBy($grantedBy);
        }
        
        $userPermission->setGranted(true);
        $userPermission->setGrantedAt(new \DateTime());
        
        $this->getEntityManager()->persist($userPermission);
        $this->getEntityManager()->flush();
        
        return $userPermission;
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission(
        User $user, 
        Permission $permission, 
        ?string $resourceType = null, 
        ?int $resourceId = null
    ): void {
        $userPermission = $this->findUserPermission($user, $permission, $resourceType, $resourceId);
        
        if ($userPermission) {
            $this->getEntityManager()->remove($userPermission);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Deny permission to user (explicit deny)
     */
    public function denyPermission(
        User $user, 
        Permission $permission, 
        ?User $grantedBy = null,
        ?string $resourceType = null, 
        ?int $resourceId = null
    ): UserPermission {
        $userPermission = $this->findUserPermission($user, $permission, $resourceType, $resourceId);
        
        if (!$userPermission) {
            $userPermission = new UserPermission();
            $userPermission->setUser($user);
            $userPermission->setPermission($permission);
            $userPermission->setResourceType($resourceType);
            $userPermission->setResourceId($resourceId);
            $userPermission->setGrantedBy($grantedBy);
        }
        
        $userPermission->setGranted(false);
        $userPermission->setGrantedAt(new \DateTime());
        
        $this->getEntityManager()->persist($userPermission);
        $this->getEntityManager()->flush();
        
        return $userPermission;
    }
}
