<?php

namespace App\Controller\Api;

use App\Entity\MarketingLead;
use App\Entity\MarketingChannel;
use App\Repository\MarketingChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/leads')]
class LeadController extends AbstractController
{
    #[Route('', name: 'api_lead_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        MarketingChannelRepository $channelRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // Basic Validation
        if (empty($data['email']) || empty($data['contactName'])) {
            return $this->json(['error' => 'Missing required fields: email, contactName'], Response::HTTP_BAD_REQUEST);
        }

        $lead = new MarketingLead();
        $lead->setEmail($data['email']);
        $lead->setContactName($data['contactName']);
        $lead->setCompanyName($data['companyName'] ?? 'Unknown');
        $lead->setPhone($data['phone'] ?? null);
        $lead->setPosition($data['position'] ?? 'Other');
        $lead->setStatus($data['status'] ?? MarketingLead::STATUS_NEW);
        
        // Handle Channel Resolution
        $channelName = $data['channel'] ?? 'Direct';
        $channel = $channelRepo->findOneBy(['name' => $channelName]);
        
        if (!$channel) {
            // Option A: Assign to a default channel if not found
            // Option B: Create the channel on the fly (Let's go with finding a fallback or creating)
            // For now, let's try to find a default "Other" or create it if it doesn't exist to prevent errors
            $channel = $channelRepo->findOneBy(['name' => 'Other']);
            if (!$channel) {
                $channel = new MarketingChannel();
                $channel->setName('Other');
                $channel->setType('other');
                $channel->setStatus('active');
                $em->persist($channel);
            }
        }
        $lead->setChannel($channel);

        // We also need a Campaign. For API leads, we might put them in a "General" campaign or null if allowed.
        // Looking at Entity, Campaign is NOT nullable: `private ?MarketingCampaign $campaign = null;` but `JoinColumn(nullable: false)`
        // We need to fetch a default campaign.
        $defaultCampaign = $em->getRepository(\App\Entity\MarketingCampaign::class)->findOneBy([]) ?? 
                           $this->createDefaultCampaign($em);
        
        $lead->setCampaign($defaultCampaign);

        $em->persist($lead);
        $em->flush();

        return $this->json([
            'message' => 'Lead created successfully',
            'id' => $lead->getId(),
            'status' => $lead->getStatus()
        ], Response::HTTP_CREATED);
    }

    private function createDefaultCampaign(EntityManagerInterface $em): \App\Entity\MarketingCampaign
    {
        $campaign = new \App\Entity\MarketingCampaign();
        $campaign->setName('General API Leads');
        $campaign->setObjective('Catch-all for API leads');
        $campaign->setStatus(\App\Entity\MarketingCampaign::STATUS_ACTIVE);
        $campaign->setStartDate(new \DateTime());
        $em->persist($campaign);
        $em->flush();
        return $campaign;
    }

    #[Route('/{id}', name: 'api_lead_update', methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $lead = $em->getRepository(MarketingLead::class)->find($id);

        if (!$lead) {
            return $this->json(['error' => 'Lead not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['companyName'])) {
            $lead->setCompanyName($data['companyName']);
        }
        if (isset($data['position'])) {
            $lead->setPosition($data['position']);
        }
        if (isset($data['phone'])) {
            $lead->setPhone($data['phone']);
        }
        // Add more fields as needed

        $em->flush();

        return $this->json([
            'message' => 'Lead updated successfully',
            'id' => $lead->getId(),
            'companyName' => $lead->getCompanyName(),
            'position' => $lead->getPosition()
        ]);
    }
}
