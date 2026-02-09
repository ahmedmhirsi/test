<?php

namespace App\Security\Voter;

use App\Entity\Meeting;
use App\Entity\User;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MeetingVoter extends Voter
{
    public const VIEW = 'MEETING_VIEW';
    public const CREATE = 'MEETING_CREATE';
    public const EDIT = 'MEETING_EDIT';
    public const DELETE = 'MEETING_DELETE';
    public const START = 'MEETING_START';
    public const MODERATE = 'MEETING_MODERATE';

    private PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::START, self::MODERATE])) {
            return false;
        }

        // For CREATE, subject can be null
        if ($attribute === self::CREATE) {
            return true;
        }

        return $subject instanceof Meeting;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Meeting|null $meeting */
        $meeting = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($user, $meeting),
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($user, $meeting),
            self::DELETE => $this->canDelete($user, $meeting),
            self::START => $this->canStart($user, $meeting),
            self::MODERATE => $this->canModerate($user, $meeting),
            default => false,
        };
    }

    private function canView(User $user, Meeting $meeting): bool
    {
        return $this->permissionService->hasPermission($user, 'meeting.view', $meeting);
    }

    private function canCreate(User $user): bool
    {
        return $this->permissionService->hasPermission($user, 'meeting.create');
    }

    private function canEdit(User $user, Meeting $meeting): bool
    {
        return $this->permissionService->hasPermission($user, 'meeting.edit', $meeting);
    }

    private function canDelete(User $user, Meeting $meeting): bool
    {
        return $this->permissionService->hasPermission($user, 'meeting.delete', $meeting);
    }

    private function canStart(User $user, Meeting $meeting): bool
    {
        return $this->permissionService->hasPermission($user, 'meeting.start', $meeting);
    }

    private function canModerate(User $user, Meeting $meeting): bool
    {
        return $this->permissionService->hasPermission($user, 'meeting.moderate', $meeting);
    }
}
