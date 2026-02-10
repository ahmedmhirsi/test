<?php

use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine.orm.entity_manager');
$repo = $entityManager->getRepository(User::class);

$user = $repo->findOneBy(['email' => 'manager@smartnexus.ai']);

if (!$user) {
    echo "User not found.\n";
    exit(1);
}

echo "User: " . $user->getEmail() . "\n";
echo "Legacy Role: " . $user->getRole() . "\n";
echo "Custom Roles Count: " . $user->getCustomRoles()->count() . "\n";
foreach ($user->getCustomRoles() as $role) {
    echo "- " . $role->getName() . "\n";
}
