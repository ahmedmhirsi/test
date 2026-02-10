<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GoogleAuthController extends AbstractController
{
    /**
     * Route pour initier la connexion Google OAuth
     */
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectGoogle(ClientRegistry $clientRegistry): Response
    {
        // Redirige vers Google pour authentification
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'email',  // Demander l'email
                'profile' // Demander le profil (nom, prénom, photo)
            ], []);
    }

    /**
     * Route callback après authentification Google
     * Cette route est gérée par le GoogleAuthenticator
     */
    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckGoogle(): Response
    {
        // Cette méthode ne sera jamais appelée directement
        // Le GoogleAuthenticator intercepte cette route
        return new Response('Cette page ne devrait jamais être affichée');
    }

    /**
     * Route pour choisir le rôle après authentification Google
     */
    #[Route('/auth/google/choose-role', name: 'google_choose_role', methods: ['GET', 'POST'])]
    public function chooseRole(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        EmailService $emailService
    ): Response {
        // Récupérer les données Google stockées en session
        $googleData = $request->getSession()->get('google_user_data');

        if (!$googleData) {
            $this->addFlash('error', 'Session expirée. Veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login');
        }

        // Si le formulaire est soumis (POST)
        if ($request->isMethod('POST')) {
            $selectedRole = $request->request->get('role');

            // Valider le rôle sélectionné
            $allowedRoles = ['ROLE_EMPLOYEE', 'ROLE_CLIENT', 'ROLE_VISITEUR'];
            if (!in_array($selectedRole, $allowedRoles)) {
                $this->addFlash('error', 'Rôle invalide sélectionné.');
                return $this->redirectToRoute('google_choose_role');
            }

            // Vérifier si l'email existe déjà
            $existingUser = $userRepository->findOneBy(['email' => $googleData['email']]);

            if ($existingUser) {
                // Si l'utilisateur existe mais n'a pas de googleId
                if (!$existingUser->getGoogleId()) {
                    $this->addFlash('error', 'Un compte existe déjà avec cet email. Veuillez vous connecter avec votre mot de passe.');
                    return $this->redirectToRoute('app_login');
                }

                // L'utilisateur existe déjà avec Google, on le connecte
                $this->addFlash('success', 'Connexion réussie !');
                // Note: La connexion sera gérée par l'authenticator
            } else {
                // Créer un nouvel utilisateur
                $user = new User();
                $user->setEmail($googleData['email']);
                $user->setGoogleId($googleData['id']);
                $user->setOauthProvider('google');
                $user->setPrenom($googleData['given_name'] ?? 'Prénom');
                $user->setNom($googleData['family_name'] ?? 'Nom');
                $user->setPhoto($googleData['picture'] ?? null);
                $user->setRoles([$selectedRole, 'ROLE_USER']);
                $user->setIsVerified(true); // Email déjà vérifié par Google
                $user->setIsActive(true);
                $user->setCreatedAt(new \DateTimeImmutable());
                
                // Mot de passe aléatoire (non utilisé pour OAuth)
                $user->setPassword(bin2hex(random_bytes(32)));

                $entityManager->persist($user);
                $entityManager->flush();

                // Envoyer l'email de bienvenue
                try {
                    $emailService->sendWelcomeEmail($user);
                } catch (\Exception $e) {
                    // Continue même si l'email échoue
                }

                $this->addFlash('success', '✅ Compte créé avec succès !');
            }

            // Nettoyer la session
            $request->getSession()->remove('google_user_data');

            // Stocker l'ID utilisateur pour connexion
            $request->getSession()->set('google_auth_user_id', $existingUser?->getId() ?? $user->getId());

            // Rediriger vers le check pour authentification finale
            return $this->redirectToRoute('connect_google_check');
        }

        // Afficher le formulaire de sélection de rôle (GET)
        return $this->render('google_auth/choose_role.html.twig', [
            'googleData' => $googleData,
        ]);
    }
}
