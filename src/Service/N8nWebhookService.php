<?php

namespace App\Service;

use App\Entity\Candidature;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * N8n Webhook Service
 * 
 * This service integrates with n8n to automate email notifications
 * based on CV matching scores. It sends candidature data to an n8n webhook
 * which then triggers the appropriate email workflow.
 * 
 * Score Threshold Logic:
 * - Score >= 70: Send acceptance/interview invitation email
 * - Score < 70: Send rejection email
 */
class N8nWebhookService
{
    // Default threshold for acceptance (can be configured)
    private const ACCEPTANCE_THRESHOLD = 70.0;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $n8nWebhookUrl = ''
    ) {
        // Webhook URL can be set via environment variable or parameter
        $this->n8nWebhookUrl = $_ENV['N8N_WEBHOOK_URL'] ?? 'http://localhost:5678/webhook/candidature';
    }

    /**
     * Process a candidature and trigger n8n workflow
     * 
     * @param Candidature $candidature The candidature to process
     * @return array Response with status and message
     */
    public function processCandidature(Candidature $candidature): array
    {
        $score = $candidature->getScoreMatchingIA() ?? 0;
        $isAccepted = $score >= self::ACCEPTANCE_THRESHOLD;

        // Prepare payload for n8n
        $payload = [
            'candidature_id' => $candidature->getId(),
            'nom_candidat' => $candidature->getNomCandidat(),
            'email_candidat' => $candidature->getEmailCandidat(),
            'score_matching' => $score,
            'poste' => $candidature->getOffreEmploi()?->getPoste() ?? 'Non spécifié',
            'type_contrat' => $candidature->getOffreEmploi()?->getTypeContrat() ?? 'Non spécifié',
            'decision' => $isAccepted ? 'accepted' : 'rejected',
            'threshold' => self::ACCEPTANCE_THRESHOLD,
            'date_depot' => $candidature->getDateDepot()?->format('Y-m-d H:i:s'),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        try {
            // Send webhook to n8n
            $response = $this->httpClient->request('POST', $this->n8nWebhookUrl, [
                'json' => $payload,
                'timeout' => 10,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logger->info('N8n webhook triggered successfully', [
                    'candidature_id' => $candidature->getId(),
                    'decision' => $payload['decision'],
                ]);

                return [
                    'success' => true,
                    'message' => $isAccepted
                        ? 'Email d\'acceptation envoyé via n8n'
                        : 'Email de refus envoyé via n8n',
                    'decision' => $payload['decision'],
                    'score' => $score,
                ];
            }

            throw new \RuntimeException('N8n returned status code: ' . $statusCode);

        } catch (\Exception $e) {
            $this->logger->error('Failed to trigger n8n webhook', [
                'candidature_id' => $candidature->getId(),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du webhook: ' . $e->getMessage(),
                'decision' => $payload['decision'],
                'score' => $score,
            ];
        }
    }

    /**
     * Simulate CV score (for demo purposes)
     * In production, this would call an AI service
     */
    public function simulateScoreMatching(Candidature $candidature): float
    {
        // Generate a random score between 30 and 100 for demo
        // In production, this would analyze the CV against job requirements
        $score = mt_rand(30, 100) + (mt_rand(0, 99) / 100);
        return round($score, 2);
    }

    /**
     * Get the acceptance threshold
     */
    public function getThreshold(): float
    {
        return self::ACCEPTANCE_THRESHOLD;
    }

    /**
     * Check if a score meets the acceptance threshold
     */
    public function meetsThreshold(float $score): bool
    {
        return $score >= self::ACCEPTANCE_THRESHOLD;
    }
}
