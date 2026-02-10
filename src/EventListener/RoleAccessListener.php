<?php

namespace App\EventListener;

use App\Controller\ViewSwitcherController;
use App\Security\UserRoles;
use App\Service\UserProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Event listener to enforce role-based route access control for the 4-role system.
 * 
 * Enforces strict separation of concerns:
 * - Admin: Backoffice only
 * - Project Manager: Frontoffice management
 * - Employee: Frontoffice execution
 * - Client: Separate view
 */
class RoleAccessListener implements EventSubscriberInterface
{
    /**
     * Public routes accessible to everyone (or handled by other logic)
     */
    private const PUBLIC_ROUTES = [
        'app_set_view',
        'app_set_user',
        'app_switcher_users',
        '_wdt',
        '_profiler',
    ];

    public function __construct(
        private UserProviderInterface $userProvider,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        // 1. Skip public/dev routes
        if (!$routeName || $this->isPublicRoute($routeName)) {
            return;
        }

        // 2. Get current user
        $session = $request->getSession();
        $userId = $session->get(
            ViewSwitcherController::SESSION_CURRENT_USER_ID,
            1 // Default to Admin
        );

        $user = $this->userProvider->getUserById($userId);

        // Fallback if user not found
        if (!$user) {
            return;
        }

        $role = $user['roles'][0] ?? UserRoles::EMPLOYEE;

        // 3. Check access based on role
        if (!$this->hasAccess($role, $routeName)) {
            // Redirect to appropriate home page for the role
            $homeRoute = $this->getHomeRoute($role);

            // Prevent infinite redirect loop
            if ($routeName === $homeRoute) {
                return;
            }

            $session->getFlashBag()->add('error', 'Accès refusé : Vous n\'avez pas les droits pour accéder à cette page.');
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate($homeRoute)));
        }
    }

    private function isPublicRoute(string $routeName): bool
    {
        foreach (self::PUBLIC_ROUTES as $publicRoute) {
            if (str_starts_with($routeName, $publicRoute)) {
                return true;
            }
        }
        return false;
    }

    private function hasAccess(string $role, string $routeName): bool
    {
        // Define allowed patterns per role
        $patterns = match ($role) {
            UserRoles::ADMIN => [
                'app_dashboard',
                'app_projet_', // Full CRUD on projects
                'app_sprint_index',
                'app_sprint_show', // Read-only sprints
                'app_tache_index',
                'app_tache_show', // Read-only tasks
                'app_jalon_index',
                'app_jalon_show', // Read-only jalons
                'app_journal_temps_index', // Read all journals
                'app_search',
            ],
            UserRoles::PROJECT_MANAGER => [
                'app_projet_index',
                'app_projet_show',
                'app_projet_edit', // Read/Edit projects (no create/delete)
                'app_sprint_', // Full CRUD sprints
                'app_tache_', // Full CRUD tasks
                'app_jalon_', // Full CRUD jalons
                'app_kanban_', // Kanban access
                'app_search',
                'app_journal_temps_index', // Read journals
            ],
            UserRoles::EMPLOYEE => [
                'app_kanban_', // Kanban access
                'app_tache_index',
                'app_tache_show', // Read tasks
                'app_sprint_index',
                'app_sprint_show', // Read sprints
                'app_journal_temps_', // Full CRUD journal
                'app_projet_index',
                'app_projet_show', // Read projects
                'app_search',
            ],
            UserRoles::CLIENT => [
                'app_client_', // Client specific routes
            ],
            default => []
        };

        // Check against patterns
        foreach ($patterns as $pattern) {
            if (str_starts_with($routeName, $pattern)) {
                // Special checks for specific route restrictions within a pattern
                // Example: Admin cannot create/delete/edit sprints even though 'app_sprint_' might be allowed in a looser config
                // strict check ensures we don't accidentally allow 'app_sprint_new' for admin if we only listed 'app_sprint_index'
                return true;
            }
        }

        return false;
    }

    private function getHomeRoute(string $role): string
    {
        return match ($role) {
            UserRoles::ADMIN => 'app_dashboard',
            UserRoles::PROJECT_MANAGER => 'app_projet_index',
            UserRoles::EMPLOYEE => 'app_kanban_index',
            UserRoles::CLIENT => 'app_client_dashboard',
            default => 'app_kanban_index',
        };
    }
}
