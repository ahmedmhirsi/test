<?php

namespace App\Controller;

use App\Entity\MarketingCampaign;
use App\Entity\MarketingChannel;
use App\Entity\MarketingLead;
use App\Entity\MarketingBudget;
use App\Entity\MarketingMessage;
use App\Form\MarketingCampaignType;
use App\Form\MarketingLeadType;
use App\Form\MarketingChannelType;
use App\Repository\MarketingCampaignRepository;
use App\Repository\MarketingChannelRepository;
use App\Repository\MarketingLeadRepository;
use App\Repository\MarketingBudgetRepository;
use App\Repository\MarketingPerformanceRepository;
use App\Service\EmailMarketingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketing')]
class MarketingController extends AbstractController
{
    #[Route('', name: 'app_marketing_dashboard')]
    public function dashboard(
        MarketingCampaignRepository $campaignRepo,
        MarketingLeadRepository $leadRepo,
        MarketingBudgetRepository $budgetRepo,
        MarketingPerformanceRepository $performanceRepo
    ): Response {
        $campaigns = $campaignRepo->findAllOrdered();
        $leadStats = $leadRepo->getStatistics();
        $budgetStats = $budgetRepo->getOverallStats();
        $performanceMetrics = $performanceRepo->getOverallMetrics();
        $campaignStats = $campaignRepo->getStatistics();

        return $this->render('marketing/dashboard.html.twig', [
            'campaigns' => $campaigns,
            'leadStats' => $leadStats,
            'budgetStats' => $budgetStats,
            'performanceMetrics' => $performanceMetrics,
            'campaignStats' => $campaignStats,
        ]);
    }

    #[Route('/campaigns', name: 'app_marketing_campaigns')]
    public function campaigns(MarketingCampaignRepository $campaignRepo): Response
    {
        $campaigns = $campaignRepo->findAllOrdered();
        $selectedCampaign = $campaigns[0] ?? null;

        return $this->render('marketing/campaigns/index.html.twig', [
            'campaigns' => $campaigns,
            'selectedCampaign' => $selectedCampaign,
        ]);
    }

    #[Route('/campaigns/new', name: 'app_marketing_campaign_new')]
    public function newCampaign(Request $request, EntityManagerInterface $em): Response
    {
        $campaign = new MarketingCampaign();
        $form = $this->createForm(MarketingCampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($campaign);
            $em->flush();

            $this->addFlash('success', 'Campaign created successfully!');
            return $this->redirectToRoute('app_marketing_campaigns');
        }

        return $this->render('marketing/campaigns/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/campaigns/{id}', name: 'app_marketing_campaign_show', requirements: ['id' => '\d+'])]
    public function showCampaign(
        MarketingCampaign $campaign,
        MarketingCampaignRepository $campaignRepo,
        MarketingBudgetRepository $budgetRepo
    ): Response {
        $campaigns = $campaignRepo->findAllOrdered();
        $budgetTotals = $budgetRepo->getTotalsByCampaign($campaign);

        return $this->render('marketing/campaigns/index.html.twig', [
            'campaigns' => $campaigns,
            'selectedCampaign' => $campaign,
            'budgetTotals' => $budgetTotals,
        ]);
    }

    #[Route('/campaigns/{id}/edit', name: 'app_marketing_campaign_edit', requirements: ['id' => '\d+'])]
    public function editCampaign(
        MarketingCampaign $campaign,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(MarketingCampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Campaign updated successfully!');
            return $this->redirectToRoute('app_marketing_campaign_show', ['id' => $campaign->getId()]);
        }

        return $this->render('marketing/campaigns/edit.html.twig', [
            'form' => $form,
            'campaign' => $campaign,
        ]);
    }

    #[Route('/campaigns/{id}/delete', name: 'app_marketing_campaign_delete', methods: ['POST'])]
    public function deleteCampaign(
        MarketingCampaign $campaign,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $campaign->getId(), $request->request->get('_token'))) {
            $em->remove($campaign);
            $em->flush();
            $this->addFlash('success', 'Campaign deleted successfully!');
        }

        return $this->redirectToRoute('app_marketing_campaigns');
    }

    #[Route('/leads', name: 'app_marketing_leads')]
    public function leads(MarketingLeadRepository $leadRepo, Request $request): Response
    {
        $status = $request->query->get('status');
        
        if ($status) {
            $leads = $leadRepo->findByStatus($status);
        } else {
            $leads = $leadRepo->findAllOrdered();
        }

        $leadCounts = $leadRepo->getCountByStatus();

        return $this->render('marketing/leads/index.html.twig', [
            'leads' => $leads,
            'leadCounts' => $leadCounts,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/leads/new', name: 'app_marketing_lead_new')]
    public function newLead(Request $request, EntityManagerInterface $em): Response
    {
        $lead = new MarketingLead();
        $form = $this->createForm(MarketingLeadType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lead);
            $em->flush();

            $this->addFlash('success', 'Lead added successfully!');
            return $this->redirectToRoute('app_marketing_leads');
        }

        return $this->render('marketing/leads/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/leads/{id}', name: 'app_marketing_lead_show', requirements: ['id' => '\d+'])]
    public function showLead(MarketingLead $lead): Response
    {
        return $this->render('marketing/leads/show.html.twig', [
            'lead' => $lead,
        ]);
    }

    #[Route('/leads/{id}/status', name: 'app_marketing_lead_update_status', methods: ['POST'])]
    public function updateLeadStatus(
        MarketingLead $lead,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $newStatus = $request->request->get('status');
        if (in_array($newStatus, [
            MarketingLead::STATUS_NEW,
            MarketingLead::STATUS_CONTACTED,
            MarketingLead::STATUS_QUALIFIED,
            MarketingLead::STATUS_CONVERTED,
            MarketingLead::STATUS_LOST,
        ])) {
            $lead->setStatus($newStatus);
            $em->flush();
            $this->addFlash('success', 'Lead status updated!');
        }

        return $this->redirectToRoute('app_marketing_leads');
    }

    #[Route('/leads/{id}/edit', name: 'app_marketing_lead_edit', methods: ['GET', 'POST'])]
    public function editLead(
        MarketingLead $lead,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(MarketingLeadType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Lead updated successfully!');
            return $this->redirectToRoute('app_marketing_lead_show', ['id' => $lead->getId()]);
        }

        return $this->render('marketing/leads/edit.html.twig', [
            'lead' => $lead,
            'form' => $form,
        ]);
    }

    #[Route('/leads/{id}/delete', name: 'app_marketing_lead_delete', methods: ['POST'])]
    public function deleteLead(
        MarketingLead $lead,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$lead->getId(), $request->request->get('_token'))) {
            $em->remove($lead);
            $em->flush();
            $this->addFlash('success', 'Lead deleted successfully!');
        }

        return $this->redirectToRoute('app_marketing_leads');
    }

    #[Route('/channels', name: 'app_marketing_channels')]
    public function channels(MarketingChannelRepository $channelRepo): Response
    {
        $channels = $channelRepo->findAllOrdered();

        return $this->render('marketing/channels/index.html.twig', [
            'channels' => $channels,
        ]);
    }

    #[Route('/channels/new', name: 'app_marketing_channel_new')]
    public function newChannel(Request $request, EntityManagerInterface $em): Response
    {
        $channel = new MarketingChannel();
        $form = $this->createForm(MarketingChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($channel);
            $em->flush();

            $this->addFlash('success', 'Channel added successfully!');
            return $this->redirectToRoute('app_marketing_channels');
        }

        return $this->render('marketing/channels/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/channels/{id}/edit', name: 'app_marketing_channel_edit', methods: ['GET', 'POST'])]
    public function editChannel(
        MarketingChannel $channel,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(MarketingChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Channel updated successfully!');
            return $this->redirectToRoute('app_marketing_channels');
        }

        return $this->render('marketing/channels/edit.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/channels/{id}/delete', name: 'app_marketing_channel_delete', methods: ['POST'])]
    public function deleteChannel(
        MarketingChannel $channel,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$channel->getId(), $request->request->get('_token'))) {
            $em->remove($channel);
            $em->flush();
            $this->addFlash('success', 'Channel deleted successfully!');
        }

        return $this->redirectToRoute('app_marketing_channels');
    }

    #[Route('/analytics', name: 'app_marketing_analytics')]
    public function analytics(
        MarketingPerformanceRepository $performanceRepo,
        MarketingLeadRepository $leadRepo,
        MarketingBudgetRepository $budgetRepo
    ): Response {
        $performanceMetrics = $performanceRepo->getOverallMetrics();
        $performanceByChannel = $performanceRepo->getPerformanceByChannel();
        $leadStats = $leadRepo->getStatistics();
        $budgetStats = $budgetRepo->getOverallStats();

        $leadTrends = $leadRepo->getLeadTrends();

        return $this->render('marketing/analytics/index.html.twig', [
            'performanceMetrics' => $performanceMetrics,
            'performanceByChannel' => $performanceByChannel,
            'leadStats' => $leadStats,
            'budgetStats' => $budgetStats,
            'leadTrends' => $leadTrends,
        ]);
    }

    #[Route('/leads/{id}/email', name: 'app_marketing_email_compose', requirements: ['id' => '\d+'])]
    public function composeEmail(MarketingLead $lead, Request $request): Response
    {
        return $this->render('marketing/emails/compose.html.twig', [
            'lead' => $lead,
            'subject' => $request->query->get('subject', ''),
            'body' => $request->query->get('body', ''),
        ]);
    }

    #[Route('/leads/{id}/email/send', name: 'app_marketing_email_send', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function sendEmail(
        MarketingLead $lead,
        Request $request,
        EmailMarketingService $emailService,
        EntityManagerInterface $em
    ): Response {
        $subject = $request->request->get('subject');
        $body = $request->request->get('body');

        if ($emailService->sendPromoEmail($lead, $subject, $body)) {
            // Update lead status to 'contacted' if it was 'new'
            if ($lead->getStatus() === MarketingLead::STATUS_NEW) {
                $lead->setStatus(MarketingLead::STATUS_CONTACTED);
                $em->flush();
            }
            $this->addFlash('success', 'Email sent successfully to ' . $lead->getContactName());
        } else {
            $this->addFlash('error', 'Failed to send email. Please check your MAILER_DSN configuration.');
        }

        return $this->redirectToRoute('app_marketing_lead_show', ['id' => $lead->getId()]);
    }

    #[Route('/campaigns/{id}/email', name: 'app_marketing_email_bulk_compose', requirements: ['id' => '\d+'])]
    public function composeBulkEmail(MarketingCampaign $campaign): Response
    {
        return $this->render('marketing/emails/bulk_compose.html.twig', [
            'campaign' => $campaign,
            'leads' => $campaign->getLeads(),
        ]);
    }

    #[Route('/campaigns/{id}/email/send', name: 'app_marketing_email_bulk_send', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function sendBulkEmail(
        MarketingCampaign $campaign,
        Request $request,
        EmailMarketingService $emailService,
        EntityManagerInterface $em
    ): Response {
        $subject = $request->request->get('subject');
        $body = $request->request->get('body');

        $leads = $campaign->getLeads()->toArray();
        $result = $emailService->sendBulkPromoEmail($leads, $subject, $body);

        // Update 'new' leads to 'contacted'
        foreach ($leads as $lead) {
            if ($lead->getStatus() === MarketingLead::STATUS_NEW) {
                $lead->setStatus(MarketingLead::STATUS_CONTACTED);
            }
        }
        $em->flush();

        $this->addFlash('success', sprintf(
            'Bulk email sent: %d successful, %d failed.',
            $result['sent'],
            $result['failed']
        ));

        return $this->redirectToRoute('app_marketing_campaign_show', ['id' => $campaign->getId()]);
    }
}
