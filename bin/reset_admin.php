<?php

use App\Entity\User;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get(UserPasswordHasherInterface::class);

$userRepository = $entityManager->getRepository(User::class);
$user = $userRepository->findOneBy(['email' => 'admin@smartnexus.com']);

if ($user) {
    $hashedPassword = $passwordHasher->hashPassword($user, 'admin123');
    $user->setPassword($hashedPassword);
    $user->setRole('Admin'); // Just in case
    $entityManager->flush();
    echo "Password updated successfully for admin@smartnexus.com\n";
    echo "New hash: " . $hashedPassword . "\n";
} else {
    echo "User admin@smartnexus.com not found. Creating new...\n";
    $user = new User();
    $user->setNom('Admin User');
    $user->setEmail('admin@smartnexus.com');
    $user->setRole('Admin');
    $user->setPassword($passwordHasher->hashPassword($user, 'admin123'));
    $entityManager->persist($user);
    $entityManager->flush();
    echo "User created successfully.\n";
}
