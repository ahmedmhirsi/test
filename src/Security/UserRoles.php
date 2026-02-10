<?php

namespace App\Security;

/**
 * User role constants for the SmartNexus application.
 * 
 * 4 distinct roles with separate interfaces:
 * - ADMIN: Backoffice - Project CRUD, Read sprints/tasks/jalons
 * - PROJECT_MANAGER: Frontoffice - Sprint/Task/Jalon CRUD, Read/Update projects
 * - EMPLOYEE: Frontoffice - Journal CRUD, Read sprints/tasks
 * - CLIENT: Separate view - Read jalons, view project progress
 */
final class UserRoles
{
    /**
     * Administrator - Backoffice
     * Can: CRUD projects, READ sprints/tasks/jalons
     */
    public const ADMIN = 'ROLE_ADMIN';

    /**
     * Project Manager - Frontoffice
     * Can: CRUD sprints/tasks/jalons, READ+UPDATE projects
     */
    public const PROJECT_MANAGER = 'ROLE_PROJECT_MANAGER';

    /**
     * Employee - Frontoffice
     * Can: CRUD journal, READ sprints/tasks
     */
    public const EMPLOYEE = 'ROLE_EMPLOYEE';

    /**
     * Client - Separate View
     * Can: READ jalons, VIEW project progress
     */
    public const CLIENT = 'ROLE_CLIENT';

    /**
     * Get all roles that can be assigned to tasks
     */
    public static function getAssignableRoles(): array
    {
        return [
            self::PROJECT_MANAGER,
            self::EMPLOYEE,
        ];
    }

    /**
     * Get all roles that can manage sprints/tasks/jalons
     */
    public static function getManagerRoles(): array
    {
        return [
            self::PROJECT_MANAGER,
        ];
    }

    /**
     * Get all internal roles (not clients)
     */
    public static function getInternalRoles(): array
    {
        return [
            self::ADMIN,
            self::PROJECT_MANAGER,
            self::EMPLOYEE,
        ];
    }

    /**
     * Get role display labels (French)
     */
    public static function getRoleLabels(): array
    {
        return [
            self::ADMIN => 'Administrateur',
            self::PROJECT_MANAGER => 'Chef de Projet',
            self::EMPLOYEE => 'Employé',
            self::CLIENT => 'Client',
        ];
    }

    /**
     * Get the layout template for a role
     */
    public static function getRoleLayout(string $role): string
    {
        return match ($role) {
            self::ADMIN => 'layout/admin.html.twig',
            self::PROJECT_MANAGER => 'layout/admin.html.twig',
            self::EMPLOYEE => 'layout/employe.html.twig',
            self::CLIENT => 'layout/client.html.twig',
            default => 'layout/employe.html.twig',
        };
    }
}
