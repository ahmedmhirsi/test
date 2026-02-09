<?php

namespace App\Controller;

use App\Entity\MarketingLead;
use App\Form\MarketingLeadType;
use App\Repository\MarketingLeadRepository;
use App\Repository\MarketingCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/assistant')]
#[IsGranted('ROLE_USER')]
class AssistantController extends AbstractController
{
    #[Route('', name: 'app_assistant_dashboard')]
    public function dashboard(
        MarketingLeadRepository $leadRepository,
        MarketingCampaignRepository $campaignRepository
    ): Response {
        // Get basic stats for the assistant dashboard
        $totalLeads = $leadRepository->count([]);
        $newLeads = $leadRepository->count(['status' => 'new']);
        $activeCampaigns = $campaignRepository->count(['status' => 'active']);
        
        // Get recent leads (last 5)
        $recentLeads = $leadRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('assistant/dashboard.html.twig', [
            'totalLeads' => $totalLeads,
            'newLeads' => $newLeads,
            'activeCampaigns' => $activeCampaigns,
            'recentLeads' => $recentLeads,
        ]);
    }

    #[Route('/leads', name: 'app_assistant_leads')]
    public function leads(MarketingLeadRepository $leadRepository): Response
    {
        $leads = $leadRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('assistant/leads/index.html.twig', [
            'leads' => $leads,
        ]);
    }

    #[Route('/leads/new', name: 'app_assistant_leads_new', methods: ['GET', 'POST'])]
    public function newLead(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $lead = new MarketingLead();
        $form = $this->createForm(MarketingLeadType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lead);
            $entityManager->flush();

            $this->addFlash('success', 'Lead créé avec succès !');
            return $this->redirectToRoute('app_assistant_leads');
        }

        return $this->render('assistant/leads/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/leads/{id}', name: 'app_assistant_leads_show', methods: ['GET'])]
    public function showLead(MarketingLead $lead): Response
    {
        return $this->render('assistant/leads/show.html.twig', [
            'lead' => $lead,
        ]);
    }
}
