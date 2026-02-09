<?php

namespace App\Controller\Api;

use App\Entity\Candidature;
use App\Repository\CandidatureRepository;
use App\Service\N8nWebhookService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API Controller for Recruitment Module
 * 
 * Provides endpoints for:
 * - Processing candidatures (trigger n8n workflow)
 * - Getting candidature data
 * - Webhook endpoint for n8n callbacks
 */
#[Route('/api')]
class ApiController extends AbstractController
{
    public function __construct(
        private N8nWebhookService $n8nService,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Process a candidature - analyze CV and trigger email workflow
     * 
     * POST /api/candidature/{id}/process
     * 
     * This endpoint:
     * 1. Calculates/simulates the CV matching score
     * 2. Updates the candidature with the score
     * 3. Triggers the n8n webhook for email notification
     */
    #[Route('/candidature/{id}/process', name: 'api_candidature_process', methods: ['POST'])]
    public function processCandidature(Candidature $candidature): JsonResponse
    {
        try {
            // If no score exists, simulate one (for demo)
            if ($candidature->getScoreMatchingIA() === null) {
                $score = $this->n8nService->simulateScoreMatching($candidature);
                $candidature->setScoreMatchingIA($score);

                // Update status based on score
                if ($this->n8nService->meetsThreshold($score)) {
                    $candidature->setStatut('Entretien');
                } else {
                    $candidature->setStatut('Refusé');
                }

                $this->entityManager->flush();
            }

            // Trigger n8n workflow - Non-blocking: we continue even if it fails
            $webhookResult = $this->n8nService->processCandidature($candidature);

            return new JsonResponse([
                'success' => true, // We return true because the core analysis succeeded
                'message' => $webhookResult['success']
                    ? $webhookResult['message']
                    : 'Analyse réussie, mais notification email échouée (n8n déconnecté)',
                'webhook_success' => $webhookResult['success'],
                'data' => [
                    'candidature_id' => $candidature->getId(),
                    'nom_candidat' => $candidature->getNomCandidat(),
                    'email_candidat' => $candidature->getEmailCandidat(),
                    'score' => $candidature->getScoreMatchingIA(),
                    'decision' => $webhookResult['decision'],
                    'statut' => $candidature->getStatut(),
                    'threshold' => $this->n8nService->getThreshold(),
                ],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get candidature details (for n8n or external systems)
     * 
     * GET /api/candidature/{id}
     */
    #[Route('/candidature/{id}', name: 'api_candidature_get', methods: ['GET'])]
    public function getCandidature(Candidature $candidature): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => [
                'id' => $candidature->getId(),
                'nom_candidat' => $candidature->getNomCandidat(),
                'email_candidat' => $candidature->getEmailCandidat(),
                'date_depot' => $candidature->getDateDepot()?->format('Y-m-d H:i:s'),
                'score_matching' => $candidature->getScoreMatchingIA(),
                'statut' => $candidature->getStatut(),
                'cv_path' => $candidature->getCvPath(),
                'lettre_motivation' => $candidature->getLettreMotivation(),
                'offre' => [
                    'id' => $candidature->getOffreEmploi()?->getId(),
                    'poste' => $candidature->getOffreEmploi()?->getPoste(),
                    'type_contrat' => $candidature->getOffreEmploi()?->getTypeContrat(),
                ],
            ],
        ]);
    }

    /**
     * List all candidatures (for admin/dashboard integrations)
     * 
     * GET /api/candidatures
     */
    #[Route('/candidatures', name: 'api_candidatures_list', methods: ['GET'])]
    public function listCandidatures(CandidatureRepository $repository): JsonResponse
    {
        $candidatures = $repository->findBy([], ['dateDepot' => 'DESC'], 50);

        $data = array_map(function (Candidature $c) {
            return [
                'id' => $c->getId(),
                'nom_candidat' => $c->getNomCandidat(),
                'email_candidat' => $c->getEmailCandidat(),
                'score_matching' => $c->getScoreMatchingIA(),
                'statut' => $c->getStatut(),
                'poste' => $c->getOffreEmploi()?->getPoste(),
                'date_depot' => $c->getDateDepot()?->format('Y-m-d'),
            ];
        }, $candidatures);

        return new JsonResponse([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * Webhook callback from n8n (to update candidature status)
     * 
     * POST /api/webhook/n8n-callback
     */
    #[Route('/webhook/n8n-callback', name: 'api_n8n_callback', methods: ['POST'])]
    public function n8nCallback(Request $request, CandidatureRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['candidature_id'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'candidature_id is required',
            ], Response::HTTP_BAD_REQUEST);
        }

        $candidature = $repository->find($data['candidature_id']);
        if (!$candidature) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Candidature not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Update status if provided
        if (isset($data['statut'])) {
            $candidature->setStatut($data['statut']);
            $this->entityManager->flush();
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Callback processed successfully',
            'candidature_id' => $candidature->getId(),
            'new_statut' => $candidature->getStatut(),
        ]);
    }

    /**
     * Health check endpoint
     * 
     * GET /api/health
     */
    #[Route('/health', name: 'api_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'module' => 'recruitment',
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }
}
