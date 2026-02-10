<?php
// create_user.php
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

$user = new User();
$user->setNom('Ahmed Mhirsi');
$user->setEmail('ahmed.mhirsi@smartnexus.ai');
$user->setRole('Member');
$user->setStatutActif(true);
$user->setStatutChannel('Active');
$user->setPassword($hasher->hashPassword($user, 'password123'));

$em->persist($user);
$em->flush();

echo "User created successfully.\n";
