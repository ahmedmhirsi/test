<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class TranslationService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $googleTranslateApiKey
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $googleTranslateApiKey;
    }

    /**
     * Translate text using Google Translate API
     *
     * @param string $text Text to translate
     * @param string $targetLanguage Target language code (e.g., 'en', 'fr', 'es')
     * @param string $sourceLanguage Source language code (default: auto-detect)
     * @return array ['translatedText' => string, 'detectedSourceLanguage' => string]
     * @throws \Exception
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'fr'): array
    {
        if (empty($text)) {
            return [
                'translatedText' => '',
                'detectedSourceLanguage' => ''
            ];
        }

        // MyMemory API limit is 500 chars. We use 450 for safety.
        if (strlen($text) > 450) {
            return $this->translateLongText($text, $targetLanguage, $sourceLanguage);
        }

        return $this->translateRequest($text, $targetLanguage, $sourceLanguage);
    }

    private function translateLongText(string $text, string $targetLanguage, string $sourceLanguage): array
    {
        // Split by sentences (punctuation followed by space)
        $sentences = preg_split('/(?<=[.?!])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            // Check if adding this sentence exceeds limit
            if (strlen($currentChunk . ' ' . $sentence) > 450) {
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $sentence;
            } else {
                $currentChunk .= ($currentChunk ? ' ' : '') . $sentence;
            }
        }
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        // Handle case where a single sentence is still too long
        $finalChunks = [];
        foreach ($chunks as $chunk) {
            if (strlen($chunk) > 450) {
                $subChunks = str_split($chunk, 450);
                $finalChunks = array_merge($finalChunks, $subChunks);
            } else {
                $finalChunks[] = $chunk;
            }
        }

        $translatedParts = [];
        foreach ($finalChunks as $chunk) {
            $result = $this->translateRequest($chunk, $targetLanguage, $sourceLanguage);
            $translatedParts[] = $result['translatedText'];
        }

        return [
            'translatedText' => implode(' ', $translatedParts),
            'detectedSourceLanguage' => $sourceLanguage
        ];
    }

    private function translateRequest(string $text, string $targetLanguage, string $sourceLanguage): array
    {
        try {
            // Utilisation de l'API MyMemory (gratuite et sans clé pour < 5000 mots/jour)
            $url = 'https://api.mymemory.translated.net/get';

            // Format de paire de langue : source|target (ex: fr|en)
            $langPair = ($sourceLanguage ?: 'fr') . '|' . $targetLanguage;

            $response = $this->httpClient->request('GET', $url, [
                'query' => [
                    'q' => $text,
                    'langpair' => $langPair
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new \Exception('MyMemory API returned status code: ' . $statusCode);
            }

            $data = $response->toArray();

            if (isset($data['responseStatus']) && $data['responseStatus'] != 200) {
                throw new \Exception($data['responseDetails'] ?? 'API Error');
            }

            if (!isset($data['responseData']['translatedText'])) {
                throw new \Exception('Invalid response from MyMemory API');
            }

            return [
                'translatedText' => $data['responseData']['translatedText'],
                'detectedSourceLanguage' => $sourceLanguage // MyMemory ne retourne pas toujours la source détectée
            ];

        } catch (\Exception $e) {
            $this->logger->error('Translation error: ' . $e->getMessage());
            throw new \Exception('Erreur de traduction : ' . $e->getMessage());
        }
    }

    /**
     * Get list of supported languages
     *
     * @return array
     */
    public function getSupportedLanguages(): array
    {
        return [
            'fr' => 'Français',
            'en' => 'English',
            'es' => 'Español',
            'ar' => 'العربية',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'zh' => '中文',
            'ja' => '日本語',
            'ru' => 'Русский',
            'nl' => 'Nederlands',
            'pl' => 'Polski',
            'tr' => 'Türkçe',
            'ko' => '한국어',
            'hi' => 'हिन्दी'
        ];
    }
}
