<?php

namespace App\Command;

use App\Service\MailtrapService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:send-mail', description: 'Send a test email with Mailtrap')]
final class SendMailCommand extends Command
{
    public function __construct(private MailtrapService $mailtrapService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Sending Test Email via Mailtrap');

        $to = 'ahmedmhirsi955@gmail.com';
        $subject = 'You are awesome!';
        $content = 'Congrats for sending test email with Mailtrap!';

        $success = $this->mailtrapService->sendEmail($to, $subject, $content);

        if ($success) {
            $io->success('Email sent successfully!');
            return Command::SUCCESS;
        }

        $io->error('Failed to send email. Check error logs.');
        return Command::FAILURE;
    }
}
