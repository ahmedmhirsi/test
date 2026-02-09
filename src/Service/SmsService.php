<?php

namespace App\Service;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;

class SmsService
{
    private Client $twilioClient;
    private string $fromNumber;
    private LoggerInterface $logger;

    public function __construct(
        string $twilioSid,
        string $twilioToken,
        string $twilioFromNumber,
        LoggerInterface $logger
    ) {
        $this->twilioClient = new Client($twilioSid, $twilioToken);
        $this->fromNumber = $twilioFromNumber;
        $this->logger = $logger;
    }

    /**
     * Envoie un code de vérification par SMS pour la réinitialisation du mot de passe
     */
    public function sendResetPasswordCode(string $phoneNumber, string $code): bool
    {
        try {
            $message = $this->twilioClient->messages->create(
                $phoneNumber,
                [
                    'from' => $this->fromNumber,
                    'body' => "Votre code de vérification SmartNexus est: {$code}\n\nCe code expire dans 15 minutes.\n\nSi vous n'avez pas demandé ce code, ignorez ce message."
                ]
            );

            $this->logger->info('SMS envoyé avec succès', [
                'sid' => $message->sid,
                'to' => $phoneNumber
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi du SMS', [
                'error' => $e->getMessage(),
                'to' => $phoneNumber
            ]);

            return false;
        }
    }

    /**
     * Génère un code de vérification à 6 chiffres
     */
    public function generateCode(): string
    {
        return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Envoie un code de vérification générique par SMS
     */
    public function sendVerificationCode(string $phoneNumber, string $code, string $purpose = 'vérification'): bool
    {
        try {
            $message = $this->twilioClient->messages->create(
                $phoneNumber,
                [
                    'from' => $this->fromNumber,
                    'body' => "Votre code de {$purpose} SmartNexus est: {$code}\n\nCe code expire dans 15 minutes."
                ]
            );

            $this->logger->info('SMS de vérification envoyé', [
                'sid' => $message->sid,
                'to' => $phoneNumber,
                'purpose' => $purpose
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi du SMS de vérification', [
                'error' => $e->getMessage(),
                'to' => $phoneNumber
            ]);

            return false;
        }
    }
}
