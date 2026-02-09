<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;

#[Route('/twilio')]
class TwilioController extends AbstractController
{
    #[Route('/call', name: 'twilio_make_call', methods: ['GET'])]
    public function makeCall(Request $request): Response
    {
        $to = $request->query->get('to');

        if (!$to) {
            return $this->json(['error' => 'Missing "to" parameter'], 400);
        }

        $sid = $_ENV['TWILIO_ACCOUNT_SID'];
        $token = $_ENV['TWILIO_AUTH_TOKEN'];
        $from = $_ENV['TWILIO_PHONE_NUMBER'];

        if (!$sid || !$token || !$from || $from === 'YOUR_TWILIO_NUMBER') {
            return $this->json(['error' => 'Twilio credentials or phone number not configured in .env'], 500);
        }

        if (!class_exists(Client::class)) {
            return $this->json([
                'error' => 'Twilio SDK not installed. Please run "composer require twilio/sdk" in your terminal.'
            ], 500);
        }

        try {
            $client = new Client($sid, $token);

            // Important: For localhost, you must use ngrok or a similar tunnel
            // to make this URL publicly accessible.
            // Example: https://your-ngrok-url.ngrok-free.app/twilio/twiml

            // Remplacez la génération automatique si elle ne marche pas avec ngrok
            // $url = $this->generateUrl('twilio_handle_twiml', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $url = 'https://shaky-numbers-peel.loca.lt/twilio/twiml';

            // Check if we are on localhost and warn/replace if needed
            // (In a real scenario, you'd replace this with your ngrok URL manually or via config)
            if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1')) {
                // FALLBACK for testing: If you have an ngrok URL, put it here manually
                // $url = 'https://YOUR_NGROK_ID.ngrok-free.app/twilio/twiml';
            }

            $call = $client->calls->create(
                $to, // To
                $from, // From
                [
                    'url' => $url
                ]
            );

            return $this->json([
                'message' => 'Call initiated',
                'call_sid' => $call->sid,
                'twiml_url' => $url
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/twiml', name: 'twilio_handle_twiml', methods: ['POST', 'GET'])]
    public function handleTwiml(): Response
    {
        $response = new VoiceResponse();

        // "Bonjour, ceci est un appel automatique depuis mon application."
        $response->say('Bonjour, ceci est un appel automatique depuis mon application.', [
            'language' => 'fr-FR',
            'voice' => 'alice' // Or another French-supporting voice
        ]);

        return new Response($response->asXML(), 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}
