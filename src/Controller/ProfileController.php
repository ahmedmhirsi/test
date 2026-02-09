<?php

namespace App\Controller;

use App\Form\ProfileFormType;
use App\Form\ChangePasswordFormType;
use App\Service\CloudinaryUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        EntityManagerInterface $entityManager,
        CloudinaryUploadService $cloudinaryService
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de la photo de profil
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                try {
                    // Supprimer l'ancienne photo de Cloudinary si elle existe
                    $oldPhotoUrl = $user->getPhoto();
                    if ($oldPhotoUrl) {
                        $publicId = $cloudinaryService->extractPublicIdFromUrl($oldPhotoUrl);
                        if ($publicId) {
                            $cloudinaryService->deleteImage($publicId);
                        }
                    }

                    // Upload la nouvelle photo vers Cloudinary
                    $imageUrl = $cloudinaryService->uploadProfilePhoto($photoFile, (string)$user->getId());
                    
                    // Sauvegarder l'URL dans la base de données
                    $user->setPhoto($imageUrl);
                    
                    $this->addFlash('success', '✅ Photo de profil mise à jour avec succès!');
                    
                } catch (\Exception $e) {
                    $this->addFlash('danger', '❌ Erreur lors de l\'upload de la photo: ' . $e->getMessage());
                    return $this->render('profile/edit.html.twig', [
                        'user' => $user,
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->flush();
            
            $this->addFlash('success', '✅ Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/password', name: 'app_profile_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            
            // Vérifier le mot de passe actuel
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->redirectToRoute('app_profile_password');
            }

            // Encoder et définir le nouveau mot de passe
            $newPassword = $form->get('newPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/password.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
