<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VerificationController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private EmailService $emailService
    ) {
    }

    #[Route('/verify/email/{token}', name: 'app_verify_email')]
    public function verifyEmail(string $token): Response
    {
        $user = $this->userRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', '❌ Lien de vérification invalide');
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isVerificationTokenValid()) {
            $this->addFlash('warning', '⏰ Le lien de vérification a expiré. Un nouveau lien vous a été envoyé.');
            
            // Générer un nouveau token et renvoyer l'email
            $user->generateVerificationToken();
            $this->entityManager->flush();
            
            try {
                $this->emailService->sendVerificationEmail($user);
            } catch (\Exception $e) {
                $this->addFlash('danger', '❌ Erreur lors de l\'envoi de l\'email de vérification');
            }
            
            return $this->redirectToRoute('app_login');
        }

        // Vérifier l'email
        $user->markAsVerified();
        $this->entityManager->flush();

        // Envoyer le welcome email après vérification réussie
        try {
            $this->emailService->sendWelcomeEmail($user);
        } catch (\Exception $e) {
            // Continue même si l'email échoue
        }

        $this->addFlash('success', '✅ Votre email a été vérifié avec succès ! Bienvenue sur SmartNexus. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify/resend', name: 'app_verify_resend')]
    public function resendVerification(Request $request): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', '❌ Vous devez être connecté pour renvoyer un email de vérification');
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('info', 'ℹ️ Votre email est déjà vérifié');
            return $this->redirectToRoute('app_dashboard');
        }

        // Générer un nouveau token
        $user->generateVerificationToken();
        $this->entityManager->flush();

        try {
            $this->emailService->sendVerificationEmail($user);
            $this->addFlash('success', '✅ Un nouvel email de vérification a été envoyé');
        } catch (\Exception $e) {
            $this->addFlash('danger', '❌ Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_dashboard');
    }
}
