<?php

namespace App\Controller;

use App\Service\FacebookAdsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketing/facebook')]
class FacebookAdsController extends AbstractController
{
    public function __construct(
        private FacebookAdsService $facebookAdsService
    ) {}

    #[Route('', name: 'app_facebook_ads_index', methods: ['GET'])]
    public function index(): Response
    {
        $isConfigured = $this->facebookAdsService->isConfigured();
        $accountInsights = $this->facebookAdsService->getAccountInsights();
        $campaigns = $this->facebookAdsService->getCampaigns();

        return $this->render('marketing/facebook/index.html.twig', [
            'isConfigured' => $isConfigured,
            'accountInsights' => $accountInsights,
            'campaigns' => $campaigns,
            'campaignCount' => count($campaigns),
            'error' => $this->facebookAdsService->getLastError(),
        ]);
    }

    #[Route('/campaigns', name: 'app_facebook_ads_campaigns', methods: ['GET'])]
    public function campaigns(): Response
    {
        $campaigns = $this->facebookAdsService->getCampaigns();

        return $this->render('marketing/facebook/campaigns.html.twig', [
            'campaigns' => $campaigns,
            'isConfigured' => $this->facebookAdsService->isConfigured(),
            'error' => $this->facebookAdsService->getLastError(),
        ]);
    }

    #[Route('/insights/{campaignId}', name: 'app_facebook_ads_insights', methods: ['GET'])]
    public function insights(string $campaignId): Response
    {
        $insights = $this->facebookAdsService->getCampaignInsights($campaignId);

        return $this->render('marketing/facebook/insights.html.twig', [
            'insights' => $insights,
            'campaignId' => $campaignId,
            'isConfigured' => $this->facebookAdsService->isConfigured(),
            'error' => $this->facebookAdsService->getLastError(),
        ]);
    }
}
