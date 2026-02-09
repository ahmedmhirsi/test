<?php

namespace App\Command;

use App\Entity\Badge;
use App\Repository\BadgeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-badges',
    description: 'Seed default badges for gamification',
)]
class SeedBadgesCommand extends Command
{
    public function __construct(
        private BadgeRepository $badgeRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $badges = [
            [
                'name' => 'Chatterbox',
                'description' => 'A envoyé 10 messages',
                'icon' => '💬',
                'points' => 50,
                'criteria' => ['messages' => 10]
            ],
            [
                'name' => 'Influencer',
                'description' => 'A envoyé 50 messages',
                'icon' => '🌟',
                'points' => 200,
                'criteria' => ['messages' => 50]
            ],
            [
                'name' => 'Contributor',
                'description' => 'A partagé un fichier',
                'icon' => '📁',
                'points' => 100,
                'criteria' => ['upload' => true]
            ]
        ];

        foreach ($badges as $data) {
            $existing = $this->badgeRepository->findOneBy(['name' => $data['name']]);
            if (!$existing) {
                $badge = new Badge();
                $badge->setName($data['name']);
                $badge->setDescription($data['description']);
                $badge->setIcon($data['icon']);
                $badge->setPoints($data['points']);
                $badge->setCriteria($data['criteria']);
                $this->entityManager->persist($badge);
                $io->text('Created badge: ' . $data['name']);
            }
        }

        $this->entityManager->flush();

        $io->success('Badges seeded successfully!');

        return Command::SUCCESS;
    }
}
