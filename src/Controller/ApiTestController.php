<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/test')]
class ApiTestController extends AbstractController
{
    #[Route('/openrouter', name: 'app_api_test_openrouter')]
    public function testOpenRouter(\App\Service\OpenRouterService $openRouterService): Response
    {
        $prompt = "Explain why PHP Symfony is a great framework in one short sentence.";
        $response = $openRouterService->chat($prompt);

        return new Response("<strong>Réponse de DeepSeek (OpenRouter) :</strong><br>" . $response);
    }

    #[Route('/mailtrap', name: 'app_api_test_mailtrap')]
    public function testMailtrap(\App\Service\MailtrapService $mailtrapService): Response
    {
        $to = 'ahmedmhirsi955@gmail.com';
        $subject = "Test Mailtrap Symfony";
        $content = "<h1>Ceci est un email de test</h1><p>Envoyé depuis SmartNexus via Mailtrap API.</p>";
        
        $success = $mailtrapService->sendEmail($to, $subject, $content);

        if ($success) {
            return new Response("Email envoyé avec succès à $to !");
        } else {
            return new Response("Échec de l'envoi Email. Vérifiez MAILTRAP_API_TOKEN dans .env", 500);
        }
    }
    #[Route('/permissions/{email}', name: 'app_api_test_permissions')]
    public function checkPermissions(
        string $email,
        \App\Repository\UserRepository $userRepository,
        \App\Service\PermissionService $permissionService
    ): \Symfony\Component\HttpFoundation\JsonResponse {
        $user = $userRepository->findOneBy(['email' => $email]);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $permissions = [
            'whiteboard.view',
            'whiteboard.create',
            'whiteboard.edit',
            'whiteboard.delete',
        ];

        $results = [];
        foreach ($permissions as $perm) {
            $results[$perm] = $permissionService->hasPermission($user, $perm);
        }

        return $this->json([
            'email' => $user->getEmail(),
            'roles' => $user->getCustomRoles()->map(fn($r) => $r->getName())->toArray(),
            'permissions' => $results
        ]);
    }
}
