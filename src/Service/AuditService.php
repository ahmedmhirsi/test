<?php

namespace App\Service;

use App\Entity\AuditLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AuditService
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function log(string $action, string $entityType, int $entityId, array $details = [], ?User $user = null): void
    {
        $auditLog = new AuditLog();
        $auditLog->setAction($action);
        $auditLog->setEntityType($entityType);
        $auditLog->setEntityId($entityId);
        $auditLog->setDetails($details);

        if (!$user) {
            $user = $this->security->getUser();
        }

        if ($user instanceof User) {
            $auditLog->setUser($user);
        }

        $this->entityManager->persist($auditLog);
        $this->entityManager->flush();
    }
}
