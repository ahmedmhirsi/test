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
    description: 'Crée les utilisateurs de test (admin, employee, candidat)',
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

        // Admin
        $admin = new User();
        $admin->setEmail('admin@smartnexus.ai');
        $admin->setNom('Administrateur');
        $admin->setPrenom('Système');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin@2026'));
        $admin->setPhoneNumber('+33601020304');
        $admin->setIsActive(true);
        $admin->setIsVerified(true);
        $admin->setBio('Administrateur principal du système SmartNexus AI');
        $this->entityManager->persist($admin);

        // Employee
        $employee = new User();
        $employee->setEmail('employee@smartnexus.ai');
        $employee->setNom('Dupont');
        $employee->setPrenom('Jean');
        $employee->setRoles(['ROLE_EMPLOYEE']);
        $employee->setPassword($this->passwordHasher->hashPassword($employee, 'Employee@2026'));
        $employee->setPhoneNumber('+33612345678');
        $employee->setIsActive(true);
        $employee->setIsVerified(true);
        $employee->setBio('Chef de projet senior avec 5 ans d\'expérience');
        $employee->setExpertise('Gestion de projet, Développement Agile');
        $this->entityManager->persist($employee);

        // Candidat
        $candidat = new User();
        $candidat->setEmail('candidat@smartnexus.ai');
        $candidat->setNom('Martin');
        $candidat->setPrenom('Sophie');
        $candidat->setRoles(['ROLE_CANDIDAT']);
        $candidat->setPassword($this->passwordHasher->hashPassword($candidat, 'Candidat@2026'));
        $candidat->setPhoneNumber('+33698765432');
        $candidat->setIsActive(true);
        $candidat->setIsVerified(true);
        $candidat->setBio('Développeur full-stack passionné par les nouvelles technologies');
        $candidat->setExpertise('PHP, Symfony, React, Vue.js, Node.js');
        $this->entityManager->persist($candidat);

        $this->entityManager->flush();

        $io->success('✅ 3 utilisateurs de test créés avec succès!');
        $io->table(
            ['Email', 'Mot de passe', 'Rôle'],
            [
                ['admin@smartnexus.ai', 'Admin@2026', 'ROLE_ADMIN'],
                ['employee@smartnexus.ai', 'Employee@2026', 'ROLE_EMPLOYEE'],
                ['candidat@smartnexus.ai', 'Candidat@2026', 'ROLE_CANDIDAT'],
            ]
        );

        return Command::SUCCESS;
    }
}
