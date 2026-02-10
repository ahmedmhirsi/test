<?php

namespace App\Controller\Api;

use App\Repository\MarketingLeadRepository;
use App\Repository\MarketingBudgetRepository;
use App\Repository\MarketingCampaignRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/reports')]
class ReportController extends AbstractController
{
    #[Route('/daily', name: 'api_report_daily', methods: ['GET'])]
    public function dailySummary(
        MarketingLeadRepository $leadRepo,
        MarketingBudgetRepository $budgetRepo,
        MarketingCampaignRepository $campaignRepo
    ): JsonResponse {
        // Get stats for today/overall
        $stats = $leadRepo->getStatistics(); // This method exists from previous work
        $activeCampaigns = $campaignRepo->count(['status' => 'active']);
        
        // We might need to add a method to BudgetRepo to get "spent today" or just total
        // For now, let's use what we have available or make a simple query
        
        $response = [
            'date' => (new \DateTime())->format('Y-m-d'),
            'leads' => [
                'total' => $stats['total'] ?? 0,
                'new' => $stats['new'] ?? 0,
                'converted' => $stats['converted'] ?? 0,
            ],
            'campaigns' => [
                'active' => $activeCampaigns,
            ],
            'budget' => [
                // Placeholder until we implement precise daily tracking
                'status' => 'Tracking Active' 
            ]
        ];

        return $this->json($response);
    }
}
