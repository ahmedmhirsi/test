<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\User;
use App\Entity\UserPermission;
use App\Repository\PermissionRepository;
use App\Repository\UserRepository;
use App\Service\PermissionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/permission')]
class PermissionController extends AbstractController
{
    private PermissionService $permissionService;
    private PermissionRepository $permissionRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PermissionService $permissionService,
        PermissionRepository $permissionRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->permissionService = $permissionService;
        $this->permissionRepository = $permissionRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_permission_index', methods: ['GET'])]
    public function index(): Response
    {
        // Check if user is admin
        // TODO: Add proper authentication check
        
        $permissions = $this->permissionRepository->findAllGroupedByResource();

        return $this->render('permission/index.html.twig', [
            'permissions' => $permissions,
        ]);
    }

    #[Route('/user/{id}', name: 'app_permission_user_permissions', methods: ['GET'])]
    public function userPermissions(User $user): Response
    {
        $permissions = $this->permissionService->getUserPermissions($user);
        $allPermissions = $this->permissionRepository->findAllGroupedByResource();

        return $this->render('permission/user_permissions.html.twig', [
            'user' => $user,
            'user_permissions' => $permissions,
            'all_permissions' => $allPermissions,
        ]);
    }

    #[Route('/grant/{userId}/{permissionId}', name: 'app_permission_grant', methods: ['POST'])]
    public function grant(
        int $userId,
        int $permissionId,
        Request $request,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->find($userId);
        $permission = $this->permissionRepository->find($permissionId);

        if (!$user || !$permission) {
            throw $this->createNotFoundException('User or Permission not found');
        }

        $resourceType = $request->request->get('resource_type');
        $resourceId = $request->request->get('resource_id');

        // Create resource object if needed
        $resource = null;
        if ($resourceType && $resourceId) {
            // TODO: Load resource based on type and ID
        }

        $currentUser = $this->getUser();
        $this->permissionService->grantPermission($user, $permission, $currentUser, $resource);

        $this->addFlash('success', sprintf(
            'Permission "%s" granted to user "%s"',
            $permission->getName(),
            $user->getNom()
        ));

        return $this->redirectToRoute('app_permission_user_permissions', ['id' => $userId]);
    }

    #[Route('/revoke/{userId}/{permissionId}', name: 'app_permission_revoke', methods: ['POST'])]
    public function revoke(
        int $userId,
        int $permissionId,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->find($userId);
        $permission = $this->permissionRepository->find($permissionId);

        if (!$user || !$permission) {
            throw $this->createNotFoundException('User or Permission not found');
        }

        $this->permissionService->revokePermission($user, $permission);

        $this->addFlash('success', sprintf(
            'Permission "%s" revoked from user "%s"',
            $permission->getName(),
            $user->getNom()
        ));

        return $this->redirectToRoute('app_permission_user_permissions', ['id' => $userId]);
    }

    #[Route('/resource/{resourceType}/{resourceId}', name: 'app_permission_resource', methods: ['GET'])]
    public function resourcePermissions(string $resourceType, int $resourceId): Response
    {
        // TODO: Load resource and show who has permissions on it
        
        return $this->render('permission/resource_permissions.html.twig', [
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
        ]);
    }
}
