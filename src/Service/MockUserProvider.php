<?php

namespace App\Service;

use App\Security\UserRoles;

/**
 * Mock implementation of UserProviderInterface for development and testing.
 * 
 * This class provides sample user data that mirrors the structure of the
 * `utilisateur` table from the "Gestion d'Utilisateurs" module.
 * 
 * IMPORTANT: Replace this with RealUserProvider after merging with the
 * user management module.
 */
class MockUserProvider implements UserProviderInterface
{
    /**
     * Sample users matching the friend's database structure
     */
    private array $mockUsers = [
        5 => [
            'id' => 5,
            'nom' => 'Administrateur',
            'prenom' => 'Système',
            'email' => 'admin@smartnexus.ai',
            'roles' => ['ROLE_ADMIN'],
            'expertise' => null,
            'is_active' => true,
        ],
        6 => [
            'id' => 6,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'employee@smartnexus.ai',
            'roles' => ['ROLE_EMPLOYEE'],
            'expertise' => 'Gestion de projet, Développement Agile',
            'is_active' => true,
        ],
        7 => [
            'id' => 7,
            'nom' => 'Martin',
            'prenom' => 'Sophie',
            'email' => 'candidat@smartnexus.ai',
            'roles' => ['ROLE_CANDIDAT'],
            'expertise' => 'PHP, Symfony, React, Vue.js, Node.js',
            'is_active' => true,
        ],
        12 => [
            'id' => 12,
            'nom' => 'Mhirsi',
            'prenom' => 'Ahmed',
            'email' => 'ahmedmhirsi955@gmail.com',
            'roles' => ['ROLE_EMPLOYEE'],
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

    public function getAssignableUsers(): array
    {
        $assignable = [];
        foreach ($this->mockUsers as $user) {
            if (
                $user['is_active'] && (
                    in_array(UserRoles::EMPLOYEE, $user['roles']) ||
                    in_array(UserRoles::ADMIN, $user['roles'])
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
            if ($user['is_active'] && in_array(UserRoles::ADMIN, $user['roles'])) {
                $managers[] = [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'fullName' => $user['prenom'] . ' ' . $user['nom'],
                ];
            }
        }
        // Also include employees as potential project managers
        foreach ($this->mockUsers as $user) {
            if (
                $user['is_active'] &&
                in_array(UserRoles::EMPLOYEE, $user['roles']) &&
                !in_array(UserRoles::ADMIN, $user['roles'])
            ) {
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
}
