<?php
// reset_password.php
require __DIR__.'/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$hasher = $container->get('security.user_password_hasher');
$userRepo = $em->getRepository(User::class);

$email = 'ahmed.mhirsi@smartnexus.ai';
$user = $userRepo->findOneBy(['email' => $email]);

if (!$user) {
    echo "User not found: $email\n";
    exit(1);
}

echo "User found: " . $user->getNom() . "\n";
echo "Current Password Hash: " . $user->getPassword() . "\n";

// Reset password
$newPassword = 'password123';
$hashedPassword = $hasher->hashPassword($user, $newPassword);
$user->setPassword($hashedPassword);

$em->persist($user);
$em->flush();

echo "Password reset to '$newPassword'.\n";
echo "New Password Hash: " . $hashedPassword . "\n";
