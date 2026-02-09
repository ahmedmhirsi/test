<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class OpenRouterService
{
    private const API_URL = 'https://openrouter.ai/api/v1/chat/completions';
    private const MODEL = 'deepseek/deepseek-r1-0528:free';

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire(env: 'OPENROUTER_API_KEY')]
        private ?string $apiKey = null,
        #[Autowire(env: 'APP_NAME')]
        private string $siteName = 'SmartNexus',
        #[Autowire(env: 'DEFAULT_URI')]
        private string $siteUrl = 'http://localhost'
    ) {
    }

    public function chat(string $prompt): string
    {
        if (empty($this->apiKey)) {
            return "Erreur: Clé API OpenRouter non configurée.";
        }

        try {
            $response = $this->client->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'HTTP-Referer' => $this->siteUrl,
                    'X-Title' => $this->siteName,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => self::MODEL,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                // Get error details if available
                $content = $response->getContent(false);
                return "Erreur OpenRouter ($statusCode): " . $content;
            }

            $content = $response->toArray();

            return $content['choices'][0]['message']['content'] ?? "Erreur: Réponse vide de l'IA.";

        } catch (\Throwable $e) {
            return "Erreur lors de la communication avec OpenRouter: " . $e->getMessage();
        }
    }
}
