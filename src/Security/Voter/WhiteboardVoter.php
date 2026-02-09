<?php

namespace App\Security\Voter;

use App\Entity\Whiteboard;
use App\Entity\User;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WhiteboardVoter extends Voter
{
    public const VIEW = 'WHITEBOARD_VIEW';
    public const CREATE = 'WHITEBOARD_CREATE';
    public const EDIT = 'WHITEBOARD_EDIT';
    public const DELETE = 'WHITEBOARD_DELETE';

    private PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE])) {
            return false;
        }

        // For CREATE, subject can be null
        if ($attribute === self::CREATE) {
            return true;
        }

        return $subject instanceof Whiteboard;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Whiteboard|null $whiteboard */
        $whiteboard = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($user, $whiteboard),
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($user, $whiteboard),
            self::DELETE => $this->canDelete($user, $whiteboard),
            default => false,
        };
    }

    private function canView(User $user, Whiteboard $whiteboard): bool
    {
        return $this->permissionService->hasPermission($user, 'whiteboard.view', $whiteboard);
    }

    private function canCreate(User $user): bool
    {
        return $this->permissionService->hasPermission($user, 'whiteboard.create');
    }

    private function canEdit(User $user, Whiteboard $whiteboard): bool
    {
        return $this->permissionService->hasPermission($user, 'whiteboard.edit', $whiteboard);
    }

    private function canDelete(User $user, Whiteboard $whiteboard): bool
    {
        return $this->permissionService->hasPermission($user, 'whiteboard.delete', $whiteboard);
    }
}
