<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Service\CloudinaryUploadService;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private EmailService $emailService
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        Security $security, 
        EntityManagerInterface $entityManager,
        CloudinaryUploadService $cloudinaryService
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de la photo de profil
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                try {
                    // Upload vers Cloudinary et obtenir l'URL
                    $imageUrl = $cloudinaryService->uploadProfilePhoto($photoFile, uniqid('temp_'));
                    $user->setPhoto($imageUrl);
                } catch (\Exception $e) {
                    $this->addFlash('warning', '⚠️ Erreur lors de l\'upload de la photo: ' . $e->getMessage());
                }
            }

            // Récupérer le type d'utilisateur depuis le formulaire
            $userType = $form->get('userType')->getData();
            if ($userType) {
                $user->setRoles([$userType]);
            } else {
                $user->setRoles(['ROLE_VISITEUR']); // Par défaut visiteur
            }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Générer un token de vérification
            $user->generateVerificationToken();
            $user->setIsVerified(false); // Email non vérifié
            $user->setIsActive(true);

            $entityManager->persist($user);
            $entityManager->flush();

            // Si une photo a été uploadée, mettre à jour avec l'ID réel
            if ($photoFile && $user->getPhoto()) {
                try {
                    // Re-upload avec l'ID utilisateur réel
                    $oldUrl = $user->getPhoto();
                    $publicId = $cloudinaryService->extractPublicIdFromUrl($oldUrl);
                    if ($publicId) {
                        $cloudinaryService->deleteImage($publicId);
                    }
                    
                    $imageUrl = $cloudinaryService->uploadProfilePhoto($photoFile, (string)$user->getId());
                    $user->setPhoto($imageUrl);
                    $entityManager->flush();
                } catch (\Exception $e) {
                    // Ignorer l'erreur de re-upload, on garde la photo temporaire
                }
            }

            // Envoyer SEULEMENT l'email de vérification (le welcome email sera envoyé après vérification)
            try {
                $this->emailService->sendVerificationEmail($user);
                $this->addFlash('success', '✅ Compte créé ! Vérifiez votre email pour activer votre compte.');
            } catch (\Exception $e) {
                $this->addFlash('warning', '⚠️ Compte créé mais erreur lors de l\'envoi de l\'email de vérification.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
