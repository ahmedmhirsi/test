<?php

namespace App\Controller\Admin;

use App\Repository\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/audit-log')]
class AuditLogController extends AbstractController
{
    #[Route('/', name: 'app_admin_audit_log_index', methods: ['GET'])]
    public function index(AuditLogRepository $auditLogRepository, Request $request): Response
    {
        // Simple pagination or limit could be added here
        $limit = 100;
        $logs = $auditLogRepository->findBy([], ['createdAt' => 'DESC'], $limit);

        return $this->render('admin/audit_log/index.html.twig', [
            'logs' => $logs,
        ]);
    }
}
