<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Envoyer un email de bienvenue
     */
    public function sendWelcomeEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('smartnexus.contact@gmail.com', 'SmartNexus'))
            ->to($user->getEmail())
            ->subject('🎉 Bienvenue sur SmartNexus !')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context([
                'user' => $user,
                'loginUrl' => $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoyer un email de vérification
     */
    public function sendVerificationEmail(User $user): void
    {
        $verificationUrl = $this->urlGenerator->generate(
            'app_verify_email',
            ['token' => $user->getVerificationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from(new Address('smartnexus.contact@gmail.com', 'SmartNexus'))
            ->to($user->getEmail())
            ->subject('✅ Vérifiez votre adresse email')
            ->htmlTemplate('emails/verification.html.twig')
            ->context([
                'user' => $user,
                'verificationUrl' => $verificationUrl,
                'expiresAt' => $user->getVerificationTokenExpiresAt(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoyer un email de réinitialisation de mot de passe
     */
    public function sendResetPasswordEmail(User $user): void
    {
        $resetUrl = $this->urlGenerator->generate(
            'app_reset_password',
            ['token' => $user->getResetPasswordToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from(new Address('smartnexus.contact@gmail.com', 'SmartNexus'))
            ->to($user->getEmail())
            ->subject('🔒 Réinitialisation de votre mot de passe')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context([
                'user' => $user,
                'resetUrl' => $resetUrl,
                'expiresAt' => $user->getResetPasswordTokenExpiresAt(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoyer un email de confirmation de changement de mot de passe
     */
    public function sendPasswordChangedEmail(User $user): void
    {
        $loginUrl = $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from(new Address('smartnexus.contact@gmail.com', 'SmartNexus'))
            ->to($user->getEmail())
            ->subject('✅ Mot de passe modifié')
            ->htmlTemplate('emails/password_changed.html.twig')
            ->context([
                'user' => $user,
                'loginUrl' => $loginUrl,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoyer un email de test
     */
    public function sendTestEmail(string $recipientEmail): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('smartnexus.contact@gmail.com', 'SmartNexus'))
            ->to($recipientEmail)
            ->subject('🧪 Test Email - SmartNexus')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context([
                'user' => (object)[
                    'fullName' => 'Test User',
                    'email' => $recipientEmail,
                ],
                'loginUrl' => $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->mailer->send($email);
    }
}
