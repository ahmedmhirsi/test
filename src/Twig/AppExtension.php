<?php

namespace App\Twig;

use App\Controller\ViewSwitcherController;
use App\Security\UserRoles;
use App\Service\UserProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

/**
 * Twig Extension for 4-role RBAC system.
 * 
 * Provides global variables for:
 * - Current user and role information
 * - Layout selection based on role
 * - Permission helpers for CRUD operations
 */
class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private UserProviderInterface $userProvider
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('user_display_name', [$this, 'getUserDisplayName']),
        ];
    }

    public function getUserDisplayName(?int $userId): string
    {
        if (!$userId) {
            return 'Non assigné';
        }
        return $this->userProvider->getUserDisplayName($userId);
    }

    public function getGlobals(): array
    {
        $session = $this->requestStack->getSession();

        // Get current user ID from session (default to Admin for dev)
        $currentUserId = $session->get(
            ViewSwitcherController::SESSION_CURRENT_USER_ID,
            1 // Default to Admin (ID 1)
        );

        // Get current user data
        $currentUser = $this->userProvider->getUserById($currentUserId);

        // If user not found, fallback to Admin
        if (!$currentUser) {
            $currentUserId = 1;
            $currentUser = $this->userProvider->getUserById($currentUserId);
            $session->set(ViewSwitcherController::SESSION_CURRENT_USER_ID, $currentUserId);
        }

        // Get primary role
        $userRole = $currentUser['roles'][0] ?? 'ROLE_EMPLOYEE';

        // Get appropriate layout based on role
        $userLayout = UserRoles::getRoleLayout($userRole);

        // Role display name
        $roleLabels = UserRoles::getRoleLabels();
        $roleDisplayName = $roleLabels[$userRole] ?? 'Inconnu';

        // Permission checks for each entity type
        $permissions = $this->getPermissions($userRole);

        // Get all available users for dev switcher
        $availableUsers = $this->getAvailableUsers();

        return [
            // User information
            'current_user' => $currentUser,
            'current_user_id' => $currentUserId,
            'user_role' => $userRole,
            'role_display_name' => $roleDisplayName,

            // Layout
            'user_layout' => $userLayout,

            // Available users for dev switcher
            'available_users' => $availableUsers,

            // Role checks
            'is_admin' => $userRole === UserRoles::ADMIN,
            'is_project_manager' => $userRole === UserRoles::PROJECT_MANAGER,
            'is_employee' => $userRole === UserRoles::EMPLOYEE,
            'is_client' => $userRole === UserRoles::CLIENT,

            // Permissions (CRUD per entity)
            'can_create_projet' => $permissions['projet']['create'],
            'can_edit_projet' => $permissions['projet']['edit'],
            'can_delete_projet' => $permissions['projet']['delete'],
            'can_read_projet' => $permissions['projet']['read'],

            'can_create_sprint' => $permissions['sprint']['create'],
            'can_edit_sprint' => $permissions['sprint']['edit'],
            'can_delete_sprint' => $permissions['sprint']['delete'],
            'can_read_sprint' => $permissions['sprint']['read'],

            'can_create_tache' => $permissions['tache']['create'],
            'can_edit_tache' => $permissions['tache']['edit'],
            'can_delete_tache' => $permissions['tache']['delete'],
            'can_read_tache' => $permissions['tache']['read'],

            'can_create_jalon' => $permissions['jalon']['create'],
            'can_edit_jalon' => $permissions['jalon']['edit'],
            'can_delete_jalon' => $permissions['jalon']['delete'],
            'can_read_jalon' => $permissions['jalon']['read'],

            'can_create_journal' => $permissions['journal']['create'],
            'can_edit_journal' => $permissions['journal']['edit'],
            'can_delete_journal' => $permissions['journal']['delete'],
            'can_read_journal' => $permissions['journal']['read'],
        ];
    }

    /**
     * Get CRUD permissions for each entity based on role
     */
    private function getPermissions(string $role): array
    {
        // Define permission matrix
        return match ($role) {
            UserRoles::ADMIN => [
                'projet' => ['create' => true, 'read' => true, 'edit' => true, 'delete' => true],
                'sprint' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false],
                'tache' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false],
                'jalon' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false],
                'journal' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
            ],
            UserRoles::PROJECT_MANAGER => [
                'projet' => ['create' => false, 'read' => true, 'edit' => true, 'delete' => false],
                'sprint' => ['create' => true, 'read' => true, 'edit' => true, 'delete' => true],
                'tache' => ['create' => true, 'read' => true, 'edit' => true, 'delete' => true],
                'jalon' => ['create' => true, 'read' => true, 'edit' => true, 'delete' => true],
                'journal' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false],
            ],
            UserRoles::EMPLOYEE => [
                'projet' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false],
                'sprint' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false],
                'tache' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false],
                'jalon' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
                'journal' => ['create' => true, 'read' => true, 'edit' => true, 'delete' => true],
            ],
            UserRoles::CLIENT => [
                'projet' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false], // Read progress only
                'sprint' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
                'tache' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
                'jalon' => ['create' => false, 'read' => true, 'edit' => false, 'delete' => false],
                'journal' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
            ],
            default => [
                'projet' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
                'sprint' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
                'tache' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
                'jalon' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
                'journal' => ['create' => false, 'read' => false, 'edit' => false, 'delete' => false],
            ],
        };
    }

    /**
     * Get available users for the dev switcher dropdown
     */
    private function getAvailableUsers(): array
    {
        $users = [];
        $roleLabels = UserRoles::getRoleLabels();

        // Get all users from provider
        foreach ($this->userProvider->getAllUsers() as $user) {
            $role = $user['roles'][0] ?? 'ROLE_EMPLOYEE';
            $user['role_label'] = $roleLabels[$role] ?? 'Inconnu';
            $users[] = $user;
        }

        return $users;
    }
}
