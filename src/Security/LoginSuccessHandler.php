<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        /** @var User $user */
        $user = $token->getUser();

        // Vérifier si l'email est vérifié (seulement pour les comptes normaux, pas OAuth)
        if (!$user->isVerified() && $user->getOauthProvider() === null) {
            // Stocker un message flash
            $request->getSession()->getFlashBag()->add(
                'warning',
                '⚠️ Veuillez vérifier votre email avant de vous connecter. Un email de vérification a été envoyé à ' . $user->getEmail()
            );

            // Rediriger vers login avec message
            return new RedirectResponse($this->router->generate('app_logout'));
        }

        // Vérifier si le compte est actif
        if (!$user->isActive()) {
            $request->getSession()->getFlashBag()->add(
                'danger',
                '❌ Votre compte a été désactivé. Contactez l\'administrateur.'
            );
            return new RedirectResponse($this->router->generate('app_logout'));
        }

        // ========== GESTION 2FA ==========
        // Note: Le scheb/2fa-bundle intercepte automatiquement le login et redirige vers /2fa
        // si l'utilisateur a la 2FA activée (isTotpAuthenticationEnabled() retourne true).
        // Ce handler est appelé APRÈS la vérification 2FA réussie.
        // On met à jour le timestamp pour la vérification périodique (tous les 7 jours)
        if ($user->is2faEnabled()) {
            $user->setLast2faCheckAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        $roles = $token->getRoleNames();

        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        if (in_array('ROLE_EMPLOYEE', $roles)) {
            return new RedirectResponse($this->router->generate('employee_dashboard'));
        }

        if (in_array('ROLE_CLIENT', $roles)) {
            return new RedirectResponse($this->router->generate('app_client_dashboard'));
        }

        return new RedirectResponse($this->router->generate('app_dashboard'));
    }
}
