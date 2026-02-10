<?php

namespace App\Service;

use App\Entity\AuditLog;
use Doctrine\ORM\EntityManagerInterface;

class AuditService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function log(string $action, string $entityType, int $entityId, array $details = [], ?string $username = 'System'): void
    {
        $auditLog = new AuditLog();
        $auditLog->setAction($action);
        $auditLog->setEntityType($entityType);
        $auditLog->setEntityId($entityId);
        $auditLog->setDetails($details);
        
        // simple string storage if AuditLog entity was updated to store username instead of User relation
        // If AuditLog still expects a User object, we need to update AuditLog entity first or set it to null if allowed.
        // Based on previous steps, AuditLog relation to User was removed.
        
        $this->entityManager->persist($auditLog);
        $this->entityManager->flush();
    }
}
