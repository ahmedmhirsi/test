<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserStatusService
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function updateStatus(User $user, string $status): void
    {
        if (!in_array($status, ['Active', 'AFK', 'Offline'])) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }

        $user->setStatus($status);
        $user->setLastSeenAt(new \DateTime());
        
        $this->entityManager->flush();
    }

    public function checkAndMoveToAFK(int $timeoutMinutes = 15): int
    {
        $threshold = new \DateTime("-$timeoutMinutes minutes");
        
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.status = :active')
            ->andWhere('u.lastSeenAt < :threshold')
            ->setParameter('active', 'Active')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($users as $user) {
            $user->setStatus('AFK');
            $count++;
        }

        if ($count > 0) {
            $this->entityManager->flush();
        }

        return $count;
    }
}
