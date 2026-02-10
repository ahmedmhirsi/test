<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MailtrapService
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(env: 'MAILER_FROM')]
        private string $fromEmail = 'smartnexus.contact@gmail.com',
        private string $fromName = 'SmartNexus'
    ) {
    }

    public function sendEmail(string $to, string $subject, string $content): bool
    {
        try {
            $email = (new Email())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($to)
                ->subject($subject)
                ->html($content)
            ;

            $this->mailer->send($email);

            return true;
        } catch (\Throwable $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }
}
