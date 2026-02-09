<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
class HeartbeatController extends AbstractController
{
    #[Route('/heartbeat', name: 'app_user_heartbeat', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function heartbeat(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        
        // Ensure user is indeed an App\Entity\User
        if ($user instanceof \App\Entity\User) {
            $user->setLastSeenAt(new \DateTime());
            $entityManager->flush();
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
