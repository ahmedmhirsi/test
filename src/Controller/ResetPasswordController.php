<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\EmailService;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private EmailService $emailService,
        private SmsService $smsService,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->userRepository->findOneBy(['email' => $email]);

            // Toujours afficher le même message pour éviter l'énumération d'emails
            $this->addFlash('success', '✅ Si cet email existe, un lien de réinitialisation a été envoyé');

            if ($user) {
                // Générer un token de réinitialisation
                $user->generateResetPasswordToken();
                $this->entityManager->flush();

                // Envoyer l'email
                try {
                    $this->emailService->sendResetPasswordEmail($user);
                } catch (\Exception $e) {
                    // Ne pas afficher d'erreur pour éviter l'énumération
                }
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/sms', name: 'app_forgot_password_sms_request')]
    public function smsRequest(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $phoneNumber = $request->request->get('phone_number');
            $user = $this->userRepository->findOneBy(['phoneNumber' => $phoneNumber]);

            // Toujours afficher le même message pour éviter l'énumération
            $this->addFlash('success', '✅ Si ce numéro existe, un code SMS a été envoyé');

            if ($user) {
                // Générer un code SMS
                $code = $user->generateSmsCode();
                $this->entityManager->flush();

                // Envoyer le SMS
                try {
                    $this->smsService->sendResetPasswordCode($phoneNumber, $code);
                } catch (\Exception $e) {
                    // Ne pas afficher d'erreur pour éviter l'énumération
                }
            }

            return $this->redirectToRoute('app_forgot_password_sms_verify');
        }

        return $this->render('reset_password/sms_request.html.twig');
    }

    #[Route('/reset-password/sms/verify', name: 'app_forgot_password_sms_verify')]
    public function smsVerify(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $phoneNumber = $request->request->get('phone_number');
            $code = $request->request->get('code');

            $user = $this->userRepository->findOneBy(['phoneNumber' => $phoneNumber]);

            if (!$user) {
                $this->addFlash('danger', '❌ Numéro de téléphone non trouvé');
                return $this->redirectToRoute('app_forgot_password_sms_request');
            }

            if (!$user->isSmsCodeValid($code)) {
                $this->addFlash('danger', '❌ Code invalide ou expiré');
                return $this->render('reset_password/sms_verify.html.twig');
            }

            // Code valide, générer un token pour le changement de mot de passe
            $user->generateResetPasswordToken();
            $user->clearSmsCode();
            $this->entityManager->flush();

            return $this->redirectToRoute('app_reset_password', ['token' => $user->getResetPasswordToken()]);
        }

        return $this->render('reset_password/sms_verify.html.twig');
    }

    #[Route('/reset-password/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token): Response
    {
        $user = $this->userRepository->findOneBy(['resetPasswordToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', '❌ Lien de réinitialisation invalide');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if (!$user->isResetPasswordTokenValid()) {
            $this->addFlash('danger', '⏰ Le lien de réinitialisation a expiré. Veuillez faire une nouvelle demande.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le nouveau mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            
            $user->setPassword($hashedPassword);
            $user->clearResetPasswordToken();
            $this->entityManager->flush();

            // Envoyer un email de confirmation
            try {
                $this->emailService->sendPasswordChangedEmail($user);
            } catch (\Exception $e) {
                // Continue même si l'email échoue
            }

            $this->addFlash('success', '✅ Votre mot de passe a été modifié avec succès !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
