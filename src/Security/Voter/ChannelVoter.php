<?php

namespace App\Security\Voter;

use App\Entity\Channel;
use App\Entity\User;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ChannelVoter extends Voter
{
    public const VIEW = 'CHANNEL_VIEW';
    public const CREATE = 'CHANNEL_CREATE';
    public const EDIT = 'CHANNEL_EDIT';
    public const DELETE = 'CHANNEL_DELETE';
    public const MODERATE = 'CHANNEL_MODERATE';
    public const INVITE = 'CHANNEL_INVITE';

    private PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Check if the attribute is one we support
        if (!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::MODERATE, self::INVITE])) {
            return false;
        }

        // For CREATE, subject can be null (creating new channel)
        if ($attribute === self::CREATE) {
            return true;
        }

        // For other operations, subject must be a Channel
        return $subject instanceof Channel;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // User must be logged in
        if (!$user instanceof User) {
            return false;
        }

        /** @var Channel|null $channel */
        $channel = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($user, $channel),
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($user, $channel),
            self::DELETE => $this->canDelete($user, $channel),
            self::MODERATE => $this->canModerate($user, $channel),
            self::INVITE => $this->canInvite($user, $channel),
            default => false,
        };
    }

    private function canView(User $user, Channel $channel): bool
    {
        return $this->permissionService->hasPermission($user, 'channel.view', $channel);
    }

    private function canCreate(User $user): bool
    {
        // Check global permission to create channels
        return $this->permissionService->hasPermission($user, 'channel.create');
    }

    private function canEdit(User $user, Channel $channel): bool
    {
        return $this->permissionService->hasPermission($user, 'channel.edit', $channel);
    }

    private function canDelete(User $user, Channel $channel): bool
    {
        return $this->permissionService->hasPermission($user, 'channel.delete', $channel);
    }

    private function canModerate(User $user, Channel $channel): bool
    {
        return $this->permissionService->hasPermission($user, 'channel.moderate', $channel);
    }

    private function canInvite(User $user, Channel $channel): bool
    {
        return $this->permissionService->hasPermission($user, 'channel.invite', $channel);
    }
}
