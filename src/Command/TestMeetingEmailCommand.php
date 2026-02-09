<?php

namespace App\Command;

use App\Entity\Meeting;
use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:test-meeting-email', description: 'Test meeting invitation email with full details')]
final class TestMeetingEmailCommand extends Command
{
    public function __construct(
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Testing Meeting Invitation Email');

        // Find a test user
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        if (!$user) {
            $io->error('No user found in database. Create a user first.');
            return Command::FAILURE;
        }

        // Create a mock meeting (not persisted for test)
        $meeting = new Meeting();
        $meeting->setTitre('Test University Project Defense');
        $meeting->setDateDebut(new \DateTime('+1 day'));
        $meeting->setGoogleMeetLink('https://meet.google.com/abc-defg-hij');

        $io->note("Sending email to: " . $user->getEmail());
        
        $this->notificationService->notifyMeetingParticipants($meeting, "Ceci est un test de l'invitation enrichie.");

        $io->success('Meeting notification triggered check Mailtrap!');

        return Command::SUCCESS;
    }
}
