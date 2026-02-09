<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service pour communiquer avec le chatbot RAG (n8n workflow)
 */
class ChatbotService
{
    private string $n8nWebhookUrl;

    public function __construct(
        private HttpClientInterface $httpClient,
        string $n8nWebhookUrl = 'http://localhost:5678/webhook/chatbot'
    ) {
        $this->n8nWebhookUrl = $n8nWebhookUrl;
    }

    /**
     * Envoie un message au chatbot et reçoit la réponse
     * 
     * @param string $message Le message de l'utilisateur
     * @param string|null $sessionId L'ID de session (généré si null)
     * @return array Résultat avec success, response, sessionId
     */
    public function sendMessage(string $message, ?string $sessionId = null): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->n8nWebhookUrl, [
                'json' => [
                    'message' => $message,
                    'sessionId' => $sessionId ?? uniqid('session_', true),
                    'timestamp' => time()
                ],
                'timeout' => 30
            ]);

            $data = $response->toArray();

            return [
                'success' => true,
                'response' => $data['response'] ?? 'Réponse du chatbot',
                'sessionId' => $sessionId ?? uniqid('session_', true)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Le chatbot est temporairement indisponible',
                'details' => $e->getMessage()
            ];
        }
    }
}
