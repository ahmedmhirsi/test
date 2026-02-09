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
    description: 'Creates default Admin and Project Manager users',
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

        $users = [
            [
                'email' => 'admin@smartnexus.com',
                'nom' => 'Super Admin',
                'role' => 'Admin',
                'password' => 'password123'
            ],
            [
                'email' => 'pm@smartnexus.com',
                'nom' => 'Project Manager',
                'role' => 'ProjectManager',
                'password' => 'password123'
            ],
            [
                'email' => 'tech@smartnexus.com',
                'nom' => 'Tech Lead',
                'role' => 'Member',
                'password' => 'password123'
            ]
        ];

        foreach ($users as $userData) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);

            if (!$user) {
                $user = new User();
                $user->setEmail($userData['email']);
                $io->note("Creating user {$userData['email']}...");
            } else {
                $io->note("Updating user {$userData['email']}...");
            }

            $user->setNom($userData['nom']);
            $user->setRole($userData['role']);
            $user->setStatutActif(true);
            
            // Hash password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $io->success('Test users created/updated successfully!');
        $io->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@smartnexus.com', 'password123'],
                ['Project Manager', 'pm@smartnexus.com', 'password123'],
                ['Member', 'tech@smartnexus.com', 'password123'],
            ]
        );

        return Command::SUCCESS;
    }
}
