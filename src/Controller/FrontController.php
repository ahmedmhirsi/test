<?php

namespace App\Controller;

use App\Repository\MarketingLeadRepository;
use App\Repository\MarketingCampaignRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/front')]
class FrontController extends AbstractController
{
    #[Route('', name: 'app_front_dashboard')]
    public function dashboard(
        MarketingLeadRepository $leadRepository,
        MarketingCampaignRepository $campaignRepository
    ): Response {
        // Stats for dashboard
        $totalLeads = $leadRepository->count([]);
        $newLeads = $leadRepository->count(['status' => 'new']);
        $qualifiedLeads = $leadRepository->count(['status' => 'qualified']);
        $convertedLeads = $leadRepository->count(['status' => 'converted']);
        
        $activeCampaigns = $campaignRepository->count(['status' => 'active']);
        $totalCampaigns = $campaignRepository->count([]);
        
        // Recent leads (last 10)
        $recentLeads = $leadRepository->findBy([], ['createdAt' => 'DESC'], 10);
        
        // Active campaigns
        $campaigns = $campaignRepository->findBy(['status' => 'active'], ['startDate' => 'DESC'], 5);

        return $this->render('front/dashboard.html.twig', [
            'totalLeads' => $totalLeads,
            'newLeads' => $newLeads,
            'qualifiedLeads' => $qualifiedLeads,
            'convertedLeads' => $convertedLeads,
            'activeCampaigns' => $activeCampaigns,
            'totalCampaigns' => $totalCampaigns,
            'recentLeads' => $recentLeads,
            'campaigns' => $campaigns,
        ]);
    }

    #[Route('/leads', name: 'app_front_leads')]
    public function leads(MarketingLeadRepository $leadRepository): Response
    {
        $leads = $leadRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('front/leads.html.twig', [
            'leads' => $leads,
        ]);
    }

    #[Route('/leads/{id}', name: 'app_front_lead_show')]
    public function showLead(int $id, MarketingLeadRepository $leadRepository): Response
    {
        $lead = $leadRepository->find($id);
        
        if (!$lead) {
            throw $this->createNotFoundException('Lead non trouvé');
        }

        return $this->render('front/lead_show.html.twig', [
            'lead' => $lead,
        ]);
    }

    #[Route('/campaigns', name: 'app_front_campaigns')]
    public function campaigns(MarketingCampaignRepository $campaignRepository): Response
    {
        $campaigns = $campaignRepository->findBy([], ['startDate' => 'DESC']);

        return $this->render('front/campaigns.html.twig', [
            'campaigns' => $campaigns,
        ]);
    }

    #[Route('/campaigns/{id}', name: 'app_front_campaign_show')]
    public function showCampaign(int $id, MarketingCampaignRepository $campaignRepository): Response
    {
        $campaign = $campaignRepository->find($id);
        
        if (!$campaign) {
            throw $this->createNotFoundException('Campagne non trouvée');
        }

        return $this->render('front/campaign_show.html.twig', [
            'campaign' => $campaign,
        ]);
    }
}
