<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-users',
    description: 'Creates test users for the application'
)]
class CreateTestUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create Admin user
        $admin = new User();
        $admin->setEmail('admin@marketing.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        
        $this->entityManager->persist($admin);
        $io->success('Admin user created: admin@marketing.com / admin123');

        // Create Assistant user
        $assistant = new User();
        $assistant->setEmail('assistant@marketing.com');
        $assistant->setRoles(['ROLE_USER']);
        $assistant->setPassword($this->passwordHasher->hashPassword($assistant, 'assistant123'));
        
        $this->entityManager->persist($assistant);
        $io->success('Assistant user created: assistant@marketing.com / assistant123');

        $this->entityManager->flush();

        $io->success('All test users created successfully!');

        return Command::SUCCESS;
    }
}
