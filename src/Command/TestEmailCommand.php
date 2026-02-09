<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-email',
    description: 'Envoyer un email de test via Mailtrap',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private EmailService $emailService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse email destinataire');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $io->title('🧪 Test d\'envoi d\'email');
        $io->text("Envoi d'un email de test à : <info>$email</info>");

        try {
            $this->emailService->sendTestEmail($email);
            $io->success('✅ Email envoyé avec succès !');
            $io->note('Vérifiez votre boîte de réception Mailtrap : https://mailtrap.io/inboxes');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors de l\'envoi : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
