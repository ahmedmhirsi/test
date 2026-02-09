<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private UserRepository $userRepository,
        private RouterInterface $router
    ) {}

    public function supports(Request $request): ?bool
    {
        // Cette authenticator s'active uniquement sur la route de callback Google
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client, $request) {
                // Récupérer les informations utilisateur depuis Google
                $googleUser = $client->fetchUserFromToken($accessToken);
                
                $email = $googleUser->getEmail();
                $googleId = $googleUser->getId();

                // Chercher l'utilisateur existant par googleId
                $user = $this->userRepository->findOneBy(['googleId' => $googleId]);

                // Si pas trouvé, chercher par email
                if (!$user) {
                    $user = $this->userRepository->findOneBy(['email' => $email]);
                }

                // Si l'utilisateur existe
                if ($user) {
                    // Si l'utilisateur existe mais n'a pas de googleId, on le lie
                    if (!$user->getGoogleId()) {
                        // Rediriger vers une page pour lier les comptes ?
                        // Pour simplifier, on refuse la connexion
                        throw new AuthenticationException('Un compte existe déjà avec cet email. Veuillez utiliser votre mot de passe.');
                    }

                    // Utilisateur existant avec Google OAuth, on le connecte
                    return $user;
                }

                // Nouvel utilisateur : stocker les données en session
                // et rediriger vers la page de sélection de rôle
                $googleData = [
                    'id' => $googleId,
                    'email' => $email,
                    'given_name' => $googleUser->getFirstName(),
                    'family_name' => $googleUser->getLastName(),
                    'picture' => $googleUser->getAvatar(),
                ];

                $request->getSession()->set('google_user_data', $googleData);

                // Lever une exception pour rediriger vers le choix de rôle
                throw new AuthenticationException('google_role_selection_required');
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Après authentification réussie, rediriger vers le dashboard
        return new RedirectResponse($this->router->generate('app_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Si c'est une demande de sélection de rôle
        if ($exception->getMessage() === 'google_role_selection_required') {
            return new RedirectResponse($this->router->generate('google_choose_role'));
        }

        // Autres erreurs
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        $request->getSession()->getFlashBag()->add('error', $message);

        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
