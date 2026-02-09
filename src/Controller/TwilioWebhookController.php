<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/twilio')]
class TwilioWebhookController extends AbstractController
{
    #[Route('/webhook', name: 'twilio_webhook', methods: ['POST'])]
    public function handleIncomingMessage(Request $request, ReclamationRepository $reclamationRepository, EntityManagerInterface $entityManager): Response
    {
        // 1. Get the message Body from Twilio
        $body = $request->request->get('Body'); // e.g., "REP #12: Bonjour, voici votre réponse."
        $from = $request->request->get('From'); // Sender's number (Admin)

        error_log("Twilio Webhook Received: Body=[$body], From=[$from]");

        if (!$body) {
            return new Response('<Response></Response>', 200, ['Content-Type' => 'text/xml']);
        }

        // 2. Parse the Body to look for "REP #ID"
        // Update regex to handle potential " or ' around the ID if copied/pasted weirdly
        // Case insensitive, handles "REP # 12 : msg", "Rep #12:msg", etc.
        if (preg_match('/REP\s*#\s*(\d+)[:\s]*(.*)/is', $body, $matches)) {
            $reclamationId = $matches[1];
            $responseMessage = trim($matches[2]);

            error_log("Regex Matched! ID=[$reclamationId], Message=[$responseMessage]");

            // 3. Find the Reclamation
            $reclamation = $reclamationRepository->find($reclamationId);

            if ($reclamation) {
                // 4. Create and Save the Response
                $reponse = new Reponse();
                $reponse->setMessage($responseMessage ?: 'Réponse automatique');
                $reponse->setReclamation($reclamation);
                $reponse->setAuteur('Admin (WhatsApp)');
                $reponse->setAuteurType('admin');
                $reponse->setDateReponse(new \DateTime());

                $entityManager->persist($reponse);

                // Update reclamation status if needed
                if ($reclamation->getStatut() === 'en_cours') {
                    $reclamation->setStatut('repondu');
                }

                $entityManager->flush();
                error_log("Response saved successfully for Reclamation #$reclamationId");
            } else {
                error_log("Reclamation #$reclamationId NOT FOUND in database.");
            }
        } else {
            error_log("Regex Check FAILED for body: [$body]. Expected format: REP #12: Message");
        }

        // Return empty TwiML to acknowledge receipt to Twilio
        return new Response('<Response></Response>', 200, ['Content-Type' => 'text/xml']);
    }
}
