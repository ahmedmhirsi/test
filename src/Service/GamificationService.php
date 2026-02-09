<?php

namespace App\Service;

use App\Entity\Badge;
use App\Entity\Message;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Repository\BadgeRepository;
use App\Repository\UserBadgeRepository;
use Doctrine\ORM\EntityManagerInterface;

class GamificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BadgeRepository $badgeRepository,
        private UserBadgeRepository $userBadgeRepository
    ) {
    }

    public function processMessage(Message $message): void
    {
        $user = $message->getUser();
        if (!$user) return;

        // 1. Add Points (e.g. 5 points per message)
        $currentPoints = $user->getPoints() ?? 0;
        $user->setPoints($currentPoints + 5);

        // 2. Check criteria for Badges
        $this->checkAndAwardBadges($user);

        // Ideally we flush here or let the controller do it?
        // Since this is called from Controller which flushes, we might need to handle it.
        // But `MessageController` flushes on persist.
        // We can flush just the user/badges here.
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function checkAndAwardBadges(User $user): void
    {
        // Simple Example: Check message count using message collection size (might be slow if lazy loaded, but easy to code)
        // Better: Count query.
        $messageCount = $user->getMessages()->count();

        if ($messageCount >= 10) {
            $this->awardBadge($user, 'Chatterbox');
        }
        
        if ($messageCount >= 50) {
            $this->awardBadge($user, 'Influencer');
        }

        // Check for file uploads?
    }

    private function awardBadge(User $user, string $badgeName): void
    {
        $badge = $this->badgeRepository->findOneBy(['name' => $badgeName]);
        if (!$badge) return;

        // Check if already awarded
        $existing = $this->userBadgeRepository->findOneBy(['user' => $user, 'badge' => $badge]);
        if ($existing) return;

        $userBadge = new UserBadge();
        $userBadge->setUser($user);
        $userBadge->setBadge($badge);
        
        $this->entityManager->persist($userBadge);
        // User points bonus for badge?
        $user->setPoints($user->getPoints() + $badge->getPoints());
    }
}
