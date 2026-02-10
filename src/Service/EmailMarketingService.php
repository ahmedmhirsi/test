<?php

namespace App\Service;

use App\Entity\MarketingLead;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailMarketingService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private LoggerInterface $logger,
        private string $senderEmail = 'noreply@smartnexus.com',
        private string $senderName = 'SmartNexus Marketing',
    ) {}

    /**
     * Send a promotional email to a single lead.
     */
    public function sendPromoEmail(MarketingLead $lead, string $subject, string $body): bool
    {
        try {
            $htmlContent = $this->twig->render('emails/promo.html.twig', [
                'contactName' => $lead->getContactName(),
                'companyName' => $lead->getCompanyName(),
                'subject' => $subject,
                'body' => $body,
            ]);

            $email = (new Email())
                ->from(sprintf('%s <%s>', $this->senderName, $this->senderEmail))
                ->to($lead->getEmail())
                ->subject($subject)
                ->html($htmlContent);

            $this->mailer->send($email);

            $this->logger->info('Promo email sent to {email}', [
                'email' => $lead->getEmail(),
                'subject' => $subject,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email to {email}: {error}', [
                'email' => $lead->getEmail(),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send a promotional email to multiple leads (bulk).
     * Returns ['sent' => int, 'failed' => int]
     */
    public function sendBulkPromoEmail(array $leads, string $subject, string $body): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($leads as $lead) {
            if ($lead instanceof MarketingLead && $lead->getEmail()) {
                if ($this->sendPromoEmail($lead, $subject, $body)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
