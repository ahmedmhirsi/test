<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\Permission;
use App\Entity\RolePermission;
use App\Repository\RoleRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoleService
{
    private RoleRepository $roleRepository;
    private PermissionRepository $permissionRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Assign a role to a user
     */
    public function assignRole(User $user, Role $role): void
    {
        if (!$user->getCustomRoles()->contains($role)) {
            $user->addCustomRole($role);
            $this->entityManager->flush();
        }
    }

    /**
     * Remove a role from a user
     */
    public function removeRole(User $user, Role $role): void
    {
        if ($user->getCustomRoles()->contains($role)) {
            $user->removeCustomRole($role);
            $this->entityManager->flush();
        }
    }

    /**
     * Get all roles for a user
     */
    public function getUserRoles(User $user): array
    {
        return $user->getCustomRoles()->toArray();
    }

    /**
     * Get all permissions for a role
     */
    public function getRolePermissions(Role $role): array
    {
        return $role->getPermissions();
    }

    /**
     * Create a new role with permissions
     */
    public function createRole(string $name, array $permissionNames, ?string $description = null, bool $isSystem = false): Role
    {
        $role = new Role();
        $role->setName($name);
        $role->setDescription($description);
        $role->setIsSystem($isSystem);

        $this->entityManager->persist($role);

        // Add permissions to role
        foreach ($permissionNames as $permissionName) {
            $permission = $this->permissionRepository->findByName($permissionName);
            if ($permission) {
                $rolePermission = new RolePermission();
                $rolePermission->setRole($role);
                $rolePermission->setPermission($permission);
                $this->entityManager->persist($rolePermission);
                $role->addRolePermission($rolePermission);
            }
        }

        $this->entityManager->flush();

        return $role;
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Role $role, array $permissionNames): void
    {
        // Remove existing permissions
        foreach ($role->getRolePermissions() as $rolePermission) {
            $this->entityManager->remove($rolePermission);
        }
        $this->entityManager->flush();

        // Add new permissions
        foreach ($permissionNames as $permissionName) {
            $permission = $this->permissionRepository->findByName($permissionName);
            if ($permission) {
                $rolePermission = new RolePermission();
                $rolePermission->setRole($role);
                $rolePermission->setPermission($permission);
                $this->entityManager->persist($rolePermission);
                $role->addRolePermission($rolePermission);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Delete a role (only if not system role)
     */
    public function deleteRole(Role $role): bool
    {
        if ($role->isSystem()) {
            return false; // Cannot delete system roles
        }

        $this->entityManager->remove($role);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Add permission to role
     */
    public function addPermissionToRole(Role $role, Permission $permission): void
    {
        // Check if permission already exists
        foreach ($role->getRolePermissions() as $rolePermission) {
            if ($rolePermission->getPermission()->getId() === $permission->getId()) {
                return; // Already exists
            }
        }

        $rolePermission = new RolePermission();
        $rolePermission->setRole($role);
        $rolePermission->setPermission($permission);
        
        $this->entityManager->persist($rolePermission);
        $role->addRolePermission($rolePermission);
        $this->entityManager->flush();
    }

    /**
     * Remove permission from role
     */
    public function removePermissionFromRole(Role $role, Permission $permission): void
    {
        foreach ($role->getRolePermissions() as $rolePermission) {
            if ($rolePermission->getPermission()->getId() === $permission->getId()) {
                $this->entityManager->remove($rolePermission);
                $role->removeRolePermission($rolePermission);
                $this->entityManager->flush();
                return;
            }
        }
    }

    /**
     * Get all users with a specific role
     */
    public function getUsersWithRole(Role $role): array
    {
        return $role->getUsers()->toArray();
    }

    /**
     * Check if user has a specific role
     */
    public function userHasRole(User $user, string $roleName): bool
    {
        return $user->hasCustomRole($roleName);
    }
}
