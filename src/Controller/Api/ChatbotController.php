<?php

namespace App\Controller\Api;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/chatbot')]
class ChatbotController extends AbstractController
{
    public function __construct(private ChatbotService $chatbotService)
    {
    }

    #[Route('/message', name: 'api_chatbot_message', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['message']) || empty(trim($data['message']))) {
            return $this->json([
                'success' => false,
                'error' => 'Le message est requis'
            ], 400);
        }

        $result = $this->chatbotService->sendMessage(
            $data['message'],
            $data['sessionId'] ?? null
        );

        return $this->json($result);
    }
}
