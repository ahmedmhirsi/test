<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Permission;
use App\Entity\Channel;
use App\Entity\Meeting;
use App\Entity\Message;
use App\Repository\PermissionRepository;
use App\Repository\UserPermissionRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PermissionService
{
    private PermissionRepository $permissionRepository;
    private UserPermissionRepository $userPermissionRepository;
    private RoleRepository $roleRepository;
    private EntityManagerInterface $entityManager;
    private ?LoggerInterface $logger;

    public function __construct(
        PermissionRepository $permissionRepository,
        UserPermissionRepository $userPermissionRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        ?LoggerInterface $logger = null
    ) {
        $this->permissionRepository = $permissionRepository;
        $this->userPermissionRepository = $userPermissionRepository;
        $this->roleRepository = $roleRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Check if user has a specific permission
     * 
     * @param User $user The user to check
     * @param string $permissionName Permission name (e.g., 'channel.edit')
     * @param object|null $resource Optional resource object (Channel, Meeting, etc.)
     * @return bool
     */
    public function hasPermission(User $user, string $permissionName, ?object $resource = null): bool
    {
        // Admin always has all permissions
        if ($user->isAdmin()) {
            return true;
        }

        // Get the permission entity
        $permission = $this->permissionRepository->findByName($permissionName);
        if (!$permission) {
            $this->logger?->warning("Permission not found: {$permissionName}");
            return false;
        }

        // Extract resource type and ID if resource provided
        $resourceType = null;
        $resourceId = null;
        if ($resource !== null) {
            $resourceType = $this->getResourceType($resource);
            $resourceId = $this->getResourceId($resource);
        }

        // Check for explicit user permission (grant or deny)
        $userPermissions = $this->userPermissionRepository->findByUserAndResourceType($user, $resourceType);
        foreach ($userPermissions as $userPerm) {
            if ($userPerm->getPermission()->getName() === $permissionName) {
                if ($userPerm->appliesTo($resourceType, $resourceId)) {
                    return $userPerm->isGranted();
                }
            }
        }

        // Check channel-specific permissions for channel resources
        if ($resource instanceof Channel) {
            return $this->checkChannelPermission($user, $resource, $permissionName);
        }

        // Check meeting permissions
        if ($resource instanceof Meeting) {
            return $this->checkMeetingPermission($user, $resource, $permissionName);
        }

        // Check message permissions
        if ($resource instanceof Message) {
            return $this->checkMessagePermission($user, $resource, $permissionName);
        }

        // Check role-based permissions
        return $this->checkRolePermissions($user, $permission);
    }

    /**
     * Check channel-specific permissions via UserChannel
     */
    private function checkChannelPermission(User $user, Channel $channel, string $permissionName): bool
    {
        // Find UserChannel relationship
        $userChannel = null;
        foreach ($user->getUserChannels() as $uc) {
            if ($uc->getChannel()->getId() === $channel->getId()) {
                $userChannel = $uc;
                break;
            }
        }

        if (!$userChannel) {
            return false; // User not in channel
        }

        // Check role in channel
        $roleInChannel = $userChannel->getRoleInChannel();
        
        // Owner has all permissions
        if ($roleInChannel === 'Owner') {
            return true;
        }

        // Moderator permissions
        if ($roleInChannel === 'Moderator') {
            $moderatorPermissions = [
                'channel.view', 'channel.moderate', 'channel.invite',
                'message.view', 'message.create', 'message.delete', 'message.pin',
                'meeting.view', 'meeting.create'
            ];
            if (in_array($permissionName, $moderatorPermissions)) {
                return true;
            }
        }

        // Check specific UserChannel permissions
        switch ($permissionName) {
            case 'channel.invite':
                return $userChannel->canInvite();
            case 'message.delete':
            case 'message.moderate':
                return $userChannel->canManageMessages();
            case 'meeting.create':
                return $userChannel->canCreateMeetings();
            case 'message.pin':
                return $userChannel->canPinMessages();
            case 'channel.view':
            case 'message.view':
            case 'message.create':
                return true; // All channel members can view and create messages
        }

        return false;
    }

    /**
     * Check meeting-specific permissions
     */
    private function checkMeetingPermission(User $user, Meeting $meeting, string $permissionName): bool
    {
        // Check if user is a participant
        $isParticipant = false;
        foreach ($meeting->getMeetingUsers() as $mu) {
            if ($mu->getUser()->getId() === $user->getId()) {
                $isParticipant = true;
                break;
            }
        }

        // Meeting creator has full permissions
        // Note: You might want to add a 'created_by' field to Meeting entity
        
        // Check channel permissions if meeting is associated with a channel
        if ($meeting->getChannelVocal() || $meeting->getChannelMessage()) {
            $channel = $meeting->getChannelVocal() ?? $meeting->getChannelMessage();
            if ($this->hasPermission($user, 'channel.moderate', $channel)) {
                return true;
            }
        }

        // Participants can view
        if ($permissionName === 'meeting.view' && $isParticipant) {
            return true;
        }

        return false;
    }

    /**
     * Check message-specific permissions
     */
    private function checkMessagePermission(User $user, Message $message, string $permissionName): bool
    {
        // User can edit/delete their own messages
        if ($permissionName === 'message.edit' || $permissionName === 'message.delete') {
            if ($message->getUser()->getId() === $user->getId()) {
                return true;
            }
        }

        // Check channel permissions
        if ($message->getChannel()) {
            return $this->checkChannelPermission($user, $message->getChannel(), $permissionName);
        }

        return false;
    }

    /**
     * Check permissions based on user's custom roles
     */
    private function checkRolePermissions(User $user, Permission $permission): bool
    {
        foreach ($user->getCustomRoles() as $role) {
            $rolePermissions = $role->getPermissions();
            foreach ($rolePermissions as $rolePerm) {
                if ($rolePerm->getId() === $permission->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(User $user, ?string $resourceType = null): array
    {
        if ($user->isAdmin()) {
            // Admin has all permissions
            return $this->permissionRepository->findAll();
        }

        $permissions = [];

        // Get permissions from custom roles
        foreach ($user->getCustomRoles() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[$permission->getName()] = $permission;
            }
        }

        // Get user-specific permissions
        $userPermissions = $this->userPermissionRepository->findByUserAndResourceType($user, $resourceType);
        foreach ($userPermissions as $userPerm) {
            if ($userPerm->isGranted()) {
                $permissions[$userPerm->getPermission()->getName()] = $userPerm->getPermission();
            } else {
                // Explicit deny removes the permission
                unset($permissions[$userPerm->getPermission()->getName()]);
            }
        }

        return array_values($permissions);
    }

    /**
     * Grant a permission to a user
     */
    public function grantPermission(
        User $user, 
        Permission $permission, 
        ?User $grantedBy = null,
        ?object $resource = null
    ): void {
        $resourceType = $resource ? $this->getResourceType($resource) : null;
        $resourceId = $resource ? $this->getResourceId($resource) : null;

        $this->userPermissionRepository->grantPermission(
            $user, 
            $permission, 
            $grantedBy,
            $resourceType, 
            $resourceId
        );
    }

    /**
     * Revoke a permission from a user
     */
    public function revokePermission(User $user, Permission $permission, ?object $resource = null): void
    {
        $resourceType = $resource ? $this->getResourceType($resource) : null;
        $resourceId = $resource ? $this->getResourceId($resource) : null;

        $this->userPermissionRepository->revokePermission($user, $permission, $resourceType, $resourceId);
    }

    /**
     * Get resource type from object
     */
    private function getResourceType(object $resource): string
    {
        $className = get_class($resource);
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Get resource ID from object
     */
    private function getResourceId(object $resource): ?int
    {
        if (method_exists($resource, 'getId')) {
            return $resource->getId();
        }
        return null;
    }

    /**
     * Get effective permissions for a user on a specific resource
     */
    public function getEffectivePermissions(User $user, object $resource): array
    {
        $allPermissions = $this->permissionRepository->findAll();
        $effectivePermissions = [];

        foreach ($allPermissions as $permission) {
            if ($this->hasPermission($user, $permission->getName(), $resource)) {
                $effectivePermissions[] = $permission;
            }
        }

        return $effectivePermissions;
    }
}
