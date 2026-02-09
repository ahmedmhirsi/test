<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use App\Repository\PermissionRepository;
use App\Service\RoleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/role')]
class RoleController extends AbstractController
{
    private RoleService $roleService;
    private RoleRepository $roleRepository;
    private PermissionRepository $permissionRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        RoleService $roleService,
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->roleService = $roleService;
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_role_index', methods: ['GET'])]
    public function index(): Response
    {
        $systemRoles = $this->roleRepository->findSystemRoles();
        $customRoles = $this->roleRepository->findCustomRoles();

        return $this->render('role/index.html.twig', [
            'system_roles' => $systemRoles,
            'custom_roles' => $customRoles,
        ]);
    }

    #[Route('/new', name: 'app_role_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($role);
            $this->entityManager->flush();

            // Add selected permissions to role
            $selectedPermissions = $form->get('permissions')->getData();
            foreach ($selectedPermissions as $permission) {
                $this->roleService->addPermissionToRole($role, $permission);
            }

            $this->addFlash('success', 'Role created successfully!');

            return $this->redirectToRoute('app_role_index');
        }

        return $this->render('role/new.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_show', methods: ['GET'])]
    public function show(Role $role): Response
    {
        $permissions = $this->roleService->getRolePermissions($role);
        $users = $this->roleService->getUsersWithRole($role);

        return $this->render('role/show.html.twig', [
            'role' => $role,
            'permissions' => $permissions,
            'users' => $users,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Role $role): Response
    {
        if ($role->isSystem()) {
            $this->addFlash('error', 'System roles cannot be edited.');
            return $this->redirectToRoute('app_role_index');
        }

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update role permissions
            $selectedPermissions = $form->get('permissions')->getData();
            $permissionNames = array_map(fn($p) => $p->getName(), $selectedPermissions->toArray());
            $this->roleService->updateRolePermissions($role, $permissionNames);

            $this->entityManager->flush();

            $this->addFlash('success', 'Role updated successfully!');

            return $this->redirectToRoute('app_role_index');
        }

        return $this->render('role/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_role_delete', methods: ['POST'])]
    public function delete(Request $request, Role $role): Response
    {
        if ($role->isSystem()) {
            $this->addFlash('error', 'System roles cannot be deleted.');
            return $this->redirectToRoute('app_role_index');
        }

        if ($this->isCsrfTokenValid('delete'.$role->getId(), $request->request->get('_token'))) {
            $success = $this->roleService->deleteRole($role);
            
            if ($success) {
                $this->addFlash('success', 'Role deleted successfully!');
            } else {
                $this->addFlash('error', 'Failed to delete role.');
            }
        }

        return $this->redirectToRoute('app_role_index');
    }

    #[Route('/{id}/assign/{userId}', name: 'app_role_assign', methods: ['POST'])]
    public function assignToUser(Role $role, int $userId, \App\Repository\UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $this->roleService->assignRole($user, $role);

        $this->addFlash('success', sprintf(
            'Role "%s" assigned to user "%s"',
            $role->getName(),
            $user->getNom()
        ));

        return $this->redirectToRoute('app_role_show', ['id' => $role->getId()]);
    }

    #[Route('/{id}/remove/{userId}', name: 'app_role_remove', methods: ['POST'])]
    public function removeFromUser(Role $role, int $userId, \App\Repository\UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $this->roleService->removeRole($user, $role);

        $this->addFlash('success', sprintf(
            'Role "%s" removed from user "%s"',
            $role->getName(),
            $user->getNom()
        ));

        return $this->redirectToRoute('app_role_show', ['id' => $role->getId()]);
    }
}
