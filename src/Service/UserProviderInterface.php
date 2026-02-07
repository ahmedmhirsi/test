<?php

namespace App\Service;

/**
 * Interface for retrieving user information.
 * 
 * This interface abstracts user data access to allow the "Gestion des Tâches et Projets"
 * module to work independently before merging with the "Gestion d'Utilisateurs" module.
 * 
 * After merge: Replace the MockUserProvider with a real implementation that queries
 * the `utilisateur` table.
 */
interface UserProviderInterface
{
    /**
     * Get user information by ID
     * 
     * @param int $userId The user ID (references utilisateur.id)
     * @return array{id: int, nom: string, prenom: string, email: string, roles: array, expertise: ?string}|null
     */
    public function getUserById(int $userId): ?array;

    /**
     * Get all users with a specific role
     * 
     * @param string $role The role to filter by (e.g., 'ROLE_EMPLOYEE')
     * @return array<int, array{id: int, nom: string, prenom: string, email: string}>
     */
    public function getUsersByRole(string $role): array;

    /**
     * Get users eligible to be assigned to tasks (employees and admins)
     * 
     * @return array<int, array{id: int, nom: string, prenom: string, fullName: string}>
     */
    public function getAssignableUsers(): array;

    /**
     * Get users eligible to manage projects (admins and potentially managers)
     * 
     * @return array<int, array{id: int, nom: string, prenom: string, fullName: string}>
     */
    public function getProjectManagers(): array;

    /**
     * Check if a user has a specific role
     * 
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function userHasRole(int $userId, string $role): bool;
}
