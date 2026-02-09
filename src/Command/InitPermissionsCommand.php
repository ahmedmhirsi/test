<?php

namespace App\Command;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\RolePermission;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-permissions',
    description: 'Initialize default permissions and roles in the database',
)]
class InitPermissionsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private PermissionRepository $permissionRepository;
    private RoleRepository $roleRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PermissionRepository $permissionRepository,
        RoleRepository $roleRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->permissionRepository = $permissionRepository;
        $this->roleRepository = $roleRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Initializing Permissions and Roles');

        // Define all permissions
        $permissions = [
            // Channel permissions
            ['name' => 'channel.view', 'resource' => 'Channel', 'action' => 'view', 'description' => 'View channels'],
            ['name' => 'channel.create', 'resource' => 'Channel', 'action' => 'create', 'description' => 'Create new channels'],
            ['name' => 'channel.edit', 'resource' => 'Channel', 'action' => 'edit', 'description' => 'Edit channel details'],
            ['name' => 'channel.delete', 'resource' => 'Channel', 'action' => 'delete', 'description' => 'Delete channels'],
            ['name' => 'channel.moderate', 'resource' => 'Channel', 'action' => 'moderate', 'description' => 'Moderate channel content'],
            ['name' => 'channel.invite', 'resource' => 'Channel', 'action' => 'invite', 'description' => 'Invite users to channel'],

            // Meeting permissions
            ['name' => 'meeting.view', 'resource' => 'Meeting', 'action' => 'view', 'description' => 'View meetings'],
            ['name' => 'meeting.create', 'resource' => 'Meeting', 'action' => 'create', 'description' => 'Create new meetings'],
            ['name' => 'meeting.edit', 'resource' => 'Meeting', 'action' => 'edit', 'description' => 'Edit meeting details'],
            ['name' => 'meeting.delete', 'resource' => 'Meeting', 'action' => 'delete', 'description' => 'Delete meetings'],
            ['name' => 'meeting.start', 'resource' => 'Meeting', 'action' => 'start', 'description' => 'Start meetings'],
            ['name' => 'meeting.moderate', 'resource' => 'Meeting', 'action' => 'moderate', 'description' => 'Moderate meeting participants'],

            // Message permissions
            ['name' => 'message.view', 'resource' => 'Message', 'action' => 'view', 'description' => 'View messages'],
            ['name' => 'message.create', 'resource' => 'Message', 'action' => 'create', 'description' => 'Send messages'],
            ['name' => 'message.edit', 'resource' => 'Message', 'action' => 'edit', 'description' => 'Edit own messages'],
            ['name' => 'message.delete', 'resource' => 'Message', 'action' => 'delete', 'description' => 'Delete messages'],
            ['name' => 'message.pin', 'resource' => 'Message', 'action' => 'pin', 'description' => 'Pin important messages'],

            // Whiteboard permissions
            ['name' => 'whiteboard.view', 'resource' => 'Whiteboard', 'action' => 'view', 'description' => 'View whiteboards'],
            ['name' => 'whiteboard.create', 'resource' => 'Whiteboard', 'action' => 'create', 'description' => 'Create whiteboards'],
            ['name' => 'whiteboard.edit', 'resource' => 'Whiteboard', 'action' => 'edit', 'description' => 'Edit whiteboards'],
            ['name' => 'whiteboard.delete', 'resource' => 'Whiteboard', 'action' => 'delete', 'description' => 'Delete whiteboards'],

            // Poll permissions
            ['name' => 'poll.view', 'resource' => 'Poll', 'action' => 'view', 'description' => 'View polls'],
            ['name' => 'poll.create', 'resource' => 'Poll', 'action' => 'create', 'description' => 'Create polls'],
            ['name' => 'poll.vote', 'resource' => 'Poll', 'action' => 'vote', 'description' => 'Vote in polls'],
            ['name' => 'poll.delete', 'resource' => 'Poll', 'action' => 'delete', 'description' => 'Delete polls'],

            // User management permissions
            ['name' => 'user.view', 'resource' => 'User', 'action' => 'view', 'description' => 'View users'],
            ['name' => 'user.create', 'resource' => 'User', 'action' => 'create', 'description' => 'Create users'],
            ['name' => 'user.edit', 'resource' => 'User', 'action' => 'edit', 'description' => 'Edit users'],
            ['name' => 'user.delete', 'resource' => 'User', 'action' => 'delete', 'description' => 'Delete users'],
        ];

        $io->section('Creating Permissions');
        $createdPermissions = [];
        foreach ($permissions as $permData) {
            $permission = $this->permissionRepository->findByName($permData['name']);
            if (!$permission) {
                $permission = new Permission();
                $permission->setName($permData['name']);
                $permission->setResource($permData['resource']);
                $permission->setAction($permData['action']);
                $permission->setDescription($permData['description']);
                $this->entityManager->persist($permission);
                $io->writeln("✓ Created permission: {$permData['name']}");
            } else {
                $io->writeln("- Permission already exists: {$permData['name']}");
            }
            $createdPermissions[$permData['name']] = $permission;
        }
        $this->entityManager->flush();

        // Define roles with their permissions
        $roles = [
            [
                'name' => 'System Admin',
                'description' => 'Full system access with all permissions',
                'is_system' => true,
                'permissions' => array_keys($createdPermissions), // All permissions
            ],
            [
                'name' => 'Project Manager',
                'description' => 'Manages projects, whiteboards, and meetings',
                'is_system' => true,
                'permissions' => [
                    'channel.view', 'channel.create', 'channel.edit', 'channel.delete', 'channel.moderate', 'channel.invite',
                    'message.view', 'message.create', 'message.edit', 'message.delete', 'message.pin',
                    'meeting.view', 'meeting.create', 'meeting.edit', 'meeting.delete', 'meeting.start', 'meeting.moderate',
                    'whiteboard.view', 'whiteboard.create', 'whiteboard.edit', 'whiteboard.delete',
                    'poll.view', 'poll.create', 'poll.vote', 'poll.delete',
                    'user.view',
                ],
            ],
            [
                'name' => 'Channel Owner',
                'description' => 'Full control over owned channels',
                'is_system' => true,
                'permissions' => [
                    'channel.view', 'channel.edit', 'channel.delete', 'channel.moderate', 'channel.invite',
                    'message.view', 'message.create', 'message.edit', 'message.delete', 'message.pin',
                    'meeting.view', 'meeting.create', 'meeting.edit', 'meeting.delete', 'meeting.start', 'meeting.moderate',
                    'whiteboard.view', 'whiteboard.create', 'whiteboard.edit', 'whiteboard.delete',
                    'poll.view', 'poll.create', 'poll.vote', 'poll.delete',
                ],
            ],
            [
                'name' => 'Moderator',
                'description' => 'Can moderate content and manage users',
                'is_system' => true,
                'permissions' => [
                    'channel.view', 'channel.moderate', 'channel.invite',
                    'message.view', 'message.create', 'message.delete', 'message.pin',
                    'meeting.view', 'meeting.create', 'meeting.moderate',
                    'whiteboard.view', 'whiteboard.create', 'whiteboard.edit',
                    'poll.view', 'poll.create', 'poll.vote',
                    'user.view',
                ],
            ],
            [
                'name' => 'Member',
                'description' => 'Basic member with standard permissions',
                'is_system' => true,
                'permissions' => [
                    'channel.view',
                    'message.view', 'message.create', 'message.edit',
                    'meeting.view', 'meeting.create',
                    'whiteboard.view', 'whiteboard.create',
                    'poll.view', 'poll.vote',
                    'user.view',
                ],
            ],
            [
                'name' => 'Viewer',
                'description' => 'Read-only access',
                'is_system' => true,
                'permissions' => [
                    'channel.view',
                    'message.view',
                    'meeting.view',
                    'whiteboard.view',
                    'poll.view',
                    'user.view',
                    'js_user_view',
                ],
            ],
        ];

        $io->section('Creating Roles');
        foreach ($roles as $roleData) {
            $role = $this->roleRepository->findByName($roleData['name']);
            if (!$role) {
                $role = new Role();
                $role->setName($roleData['name']);
                $role->setDescription($roleData['description']);
                $role->setIsSystem($roleData['is_system']);
                $this->entityManager->persist($role);
                $this->entityManager->flush(); // Flush to generate ID
                $io->writeln("✓ Created role: {$roleData['name']}");

                // Add permissions to role
                foreach ($roleData['permissions'] as $permName) {
                    if (isset($createdPermissions[$permName])) {
                        $rolePermission = new RolePermission();
                        $rolePermission->setRole($role);
                        $rolePermission->setPermission($createdPermissions[$permName]);
                        $this->entityManager->persist($rolePermission);
                        $role->addRolePermission($rolePermission);
                    }
                }
                $this->entityManager->flush(); // Flush role permissions
            } else {
                $io->writeln("- Role already exists: {$roleData['name']}");
            }
        }

        // Auto-assign Project Manager role to existing users
        $pmRole = $this->roleRepository->findByName('Project Manager');
        if ($pmRole) {
            $userRepository = $this->entityManager->getRepository(\App\Entity\User::class);
            $pms = $userRepository->findBy(['role' => 'ProjectManager']);
            
            foreach ($pms as $pm) {
                if (!$pm->hasCustomRole('Project Manager')) {
                    $pm->addCustomRole($pmRole);
                    $io->writeln("✓ Assigned 'Project Manager' role to user: {$pm->getEmail()}");
                }
            }
            $this->entityManager->flush();
        }

        $io->success('Permissions and roles initialized successfully!');
        $io->info(sprintf('Created %d permissions and %d roles', count($permissions), count($roles)));

        return Command::SUCCESS;
    }
}
