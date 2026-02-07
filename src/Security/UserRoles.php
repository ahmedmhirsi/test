<?php

namespace App\Security;

/**
 * User role constants matching the "Gestion d'Utilisateurs" module.
 * 
 * These roles are stored in the `utilisateur.roles` JSON column and
 * determine what actions users can perform in the application.
 */
final class UserRoles
{
    /**
     * System administrator - Full access to all features
     */
    public const ADMIN = 'ROLE_ADMIN';

    /**
     * Employee - Can be assigned to tasks, log time, manage projects (based on permissions)
     */
    public const EMPLOYEE = 'ROLE_EMPLOYEE';

    /**
     * Candidate - Limited access, primarily for recruitment module
     */
    public const CANDIDAT = 'ROLE_CANDIDAT';

    /**
     * Client - External user with view-only access to their projects
     */
    public const CLIENT = 'ROLE_CLIENT';

    /**
     * Get all roles that can be assigned to tasks
     */
    public static function getAssignableRoles(): array
    {
        return [
            self::ADMIN,
            self::EMPLOYEE,
        ];
    }

    /**
     * Get all roles that can manage projects
     */
    public static function getManagerRoles(): array
    {
        return [
            self::ADMIN,
            self::EMPLOYEE, // Employees can also manage projects they're assigned to
        ];
    }

    /**
     * Get role display labels (French)
     */
    public static function getRoleLabels(): array
    {
        return [
            self::ADMIN => 'Administrateur',
            self::EMPLOYEE => 'Employé',
            self::CANDIDAT => 'Candidat',
            self::CLIENT => 'Client',
        ];
    }
}
