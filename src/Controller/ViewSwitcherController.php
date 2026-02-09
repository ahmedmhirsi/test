<?php

namespace App\Controller;

use App\Service\UserProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Controller for switching between Admin (backoffice) and Employé (frontoffice) views.
 * 
 * For development: Also handles simulating different users.
 * After integration: The setCurrentUser method can be removed, and real auth used.
 */
#[Route('/view-switcher')]
class ViewSwitcherController extends AbstractController
{
    public const SESSION_CURRENT_USER_ID = 'current_user_id';
    public const SESSION_CURRENT_VIEW = 'current_view';

    public const VIEW_ADMIN = 'admin';
    public const VIEW_EMPLOYE = 'employe';

    // Default user ID for development (Admin)
    public const DEFAULT_USER_ID = 5;

    public function __construct(
        private UserProviderInterface $userProvider
    ) {
    }

    /**
     * Switch between admin and employee views
     */
    #[Route('/set-view/{view}', name: 'app_set_view', methods: ['GET'])]
    public function setView(Request $request, string $view, SessionInterface $session): RedirectResponse
    {
        // Validate view
        if (!in_array($view, [self::VIEW_ADMIN, self::VIEW_EMPLOYE])) {
            $view = self::VIEW_EMPLOYE;
        }

        // Get current user
        $userId = $session->get(self::SESSION_CURRENT_USER_ID, self::DEFAULT_USER_ID);
        $user = $this->userProvider->getUserById($userId);

        // Check if user can access the requested view
        if ($view === self::VIEW_ADMIN && $user) {
            // Only admins can access admin view
            if (!in_array('ROLE_ADMIN', $user['roles'] ?? [])) {
                $this->addFlash('error', 'Vous n\'avez pas accès à la vue Admin.');
                $view = self::VIEW_EMPLOYE;
            }
        }

        // Store view in session
        $session->set(self::SESSION_CURRENT_VIEW, $view);

        // Redirect to appropriate home page
        $redirectRoute = $view === self::VIEW_ADMIN ? 'app_dashboard' : 'app_kanban_index';

        // If referer exists and is from our site, redirect back
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, $request->getHost())) {
            return new RedirectResponse($referer);
        }

        return $this->redirectToRoute($redirectRoute);
    }

    /**
     * For development: Switch the simulated current user
     */
    #[Route('/set-user/{userId}', name: 'app_set_user', methods: ['GET'])]
    public function setCurrentUser(Request $request, int $userId, SessionInterface $session): RedirectResponse
    {
        // Validate user exists
        $user = $this->userProvider->getUserById($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_dashboard');
        }

        // Store user ID in session
        $session->set(self::SESSION_CURRENT_USER_ID, $userId);

        // Auto-set view based on user role
        if (in_array('ROLE_ADMIN', $user['roles'] ?? [])) {
            $session->set(self::SESSION_CURRENT_VIEW, self::VIEW_ADMIN);
            $redirectRoute = 'app_dashboard';
        } else {
            $session->set(self::SESSION_CURRENT_VIEW, self::VIEW_EMPLOYE);
            $redirectRoute = 'app_kanban_index';
        }

        $this->addFlash('success', sprintf('Connecté en tant que %s %s', $user['prenom'], $user['nom']));

        // Redirect to appropriate home
        return $this->redirectToRoute($redirectRoute);
    }

    /**
     * Get list of available users for the switcher dropdown (dev only)
     */
    #[Route('/users', name: 'app_switcher_users', methods: ['GET'])]
    public function getAvailableUsers(): Response
    {
        $users = [];

        // Get admins
        foreach ($this->userProvider->getUsersByRole('ROLE_ADMIN') as $user) {
            $users[] = $user;
        }

        // Get employees
        foreach ($this->userProvider->getUsersByRole('ROLE_EMPLOYEE') as $user) {
            $users[] = $user;
        }

        return $this->json($users);
    }
}
