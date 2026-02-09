<?php

namespace App\EventSubscriber;

use App\Entity\Message;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Repository\BadgeRepository;
use App\Repository\MessageRepository;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class GamificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BadgeRepository $badgeRepository,
        private MessageRepository $messageRepository
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Message) {
            $this->handleMessageGamification($entity);
        }
        
        // Add more handlers for other entities (e.g., PollVote)
    }

    private function handleMessageGamification(Message $message): void
    {
        $user = $message->getUser();
        if (!$user) return;

        $entityManager = $message->getRepository()->getEntityManager(); // or retrieve differently access

        // 1. Award Points (e.g. 5 points per message)
        $currentPoints = $user->getPoints() ?? 0;
        $user->setPoints($currentPoints + 5);

        // 2. Check for "Chatterbox" Badge (e.g. 10 messages)
        // Count messages is expensive on every post, maybe optimize?
        // For prototype, we check criteria.
        
        // This logic should ideally be in a service, simplifying for now.
        // We need to flush user update. But we are in postPersist, flushing inside can be tricky.
        // Usually safe if entity is different.
        
        // $entityManager->flush(); // DANGER: Infinite loop possibility if not careful or nested events.
        // Better: Rely on UnitOfWork or specialized job.
        // For simplicity, we just modify the user and let the next flush handle it?
        // No, postPersist happens AFTER flush. Modifying object here needs another flush.
        
        // Simple workaround: Execute direct UPDATE query or schedule extra flush?
        // Let's modify standard flow: Use Service called from Controller instead of Listener for simplicity and safety.
    }
}
