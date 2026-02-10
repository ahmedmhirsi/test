<?php

namespace App\Service;

use App\Security\UserRoles;

/**
 * Mock implementation of UserProviderInterface for development and testing.
 * 
 * This class provides sample user data with 4 distinct roles:
 * - ROLE_ADMIN: Backoffice - Project CRUD, Read sprints/tasks/jalons
 * - ROLE_PROJECT_MANAGER: Frontoffice - Sprint/Task/Jalon CRUD, Read/Update projects
 * - ROLE_EMPLOYEE: Frontoffice - Journal CRUD, Read sprints/tasks
 * - ROLE_CLIENT: Separate view - Read jalons, view project progress
 */
class MockUserProvider implements UserProviderInterface
{
    /**
     * Sample users - one per role for testing
     */
    private array $mockUsers = [
        // Admin - Backoffice
        1 => [
            'id' => 1,
            'nom' => 'Admin',
            'prenom' => 'Sophie',
            'email' => 'admin@smartnexus.ai',
            'roles' => ['ROLE_ADMIN'],
            'expertise' => 'Administration Système',
            'is_active' => true,
        ],
        // Project Manager - Frontoffice
        2 => [
            'id' => 2,
            'nom' => 'Manager',
            'prenom' => 'Marc',
            'email' => 'manager@smartnexus.ai',
            'roles' => ['ROLE_PROJECT_MANAGER'],
            'expertise' => 'Gestion de Projet, Scrum Master',
            'is_active' => true,
        ],
        // Employee - Frontoffice
        3 => [
            'id' => 3,
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'email' => 'employee@smartnexus.ai',
            'roles' => ['ROLE_EMPLOYEE'],
            'expertise' => 'Développement PHP, Symfony',
            'is_active' => true,
        ],
        // Client - Separate view
        4 => [
            'id' => 4,
            'nom' => 'Client',
            'prenom' => 'Claude',
            'email' => 'client@example.com',
            'roles' => ['ROLE_CLIENT'],
            'expertise' => null,
            'is_active' => true,
        ],
    ];

    public function getUserById(int $userId): ?array
    {
        return $this->mockUsers[$userId] ?? null;
    }

    public function getUsersByRole(string $role): array
    {
        return array_filter($this->mockUsers, function ($user) use ($role) {
            return in_array($role, $user['roles']);
        });
    }

    public function getAllUsers(): array
    {
        return $this->mockUsers;
    }

    public function getAssignableUsers(): array
    {
        $assignable = [];
        foreach ($this->mockUsers as $user) {
            // Employees can be assigned to tasks
            if (
                $user['is_active'] && (
                    in_array('ROLE_EMPLOYEE', $user['roles'])
                )
            ) {
                $assignable[] = [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'fullName' => $user['prenom'] . ' ' . $user['nom'],
                ];
            }
        }
        return $assignable;
    }

    public function getProjectManagers(): array
    {
        $managers = [];
        foreach ($this->mockUsers as $user) {
            // Only actual project managers
            if ($user['is_active'] && in_array('ROLE_PROJECT_MANAGER', $user['roles'])) {
                $managers[] = [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'fullName' => $user['prenom'] . ' ' . $user['nom'],
                ];
            }
        }
        return $managers;
    }

    public function userHasRole(int $userId, string $role): bool
    {
        $user = $this->getUserById($userId);
        if ($user === null) {
            return false;
        }
        return in_array($role, $user['roles']);
    }

    /**
     * Get the primary role for a user (first role in array)
     */
    public function getUserPrimaryRole(int $userId): ?string
    {
        $user = $this->getUserById($userId);
        if ($user === null || empty($user['roles'])) {
            return null;
        }
        return $user['roles'][0];
    }

    /**
     * Helper method to get user display name
     */
    public function getUserDisplayName(int $userId): string
    {
        $user = $this->getUserById($userId);
        if ($user === null) {
            return 'Utilisateur #' . $userId;
        }
        return $user['prenom'] . ' ' . $user['nom'];
    }

    /**
     * Get role display name in French
     */
    public function getRoleDisplayName(string $role): string
    {
        return match ($role) {
            'ROLE_ADMIN' => 'Administrateur',
            'ROLE_PROJECT_MANAGER' => 'Chef de Projet',
            'ROLE_EMPLOYEE' => 'Employé',
            'ROLE_CLIENT' => 'Client',
            default => 'Inconnu',
        };
    }
}
