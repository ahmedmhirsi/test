<?php

namespace App\Security\Voter;

use App\Entity\Message;
use App\Entity\User;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter
{
    public const VIEW = 'MESSAGE_VIEW';
    public const CREATE = 'MESSAGE_CREATE';
    public const EDIT = 'MESSAGE_EDIT';
    public const DELETE = 'MESSAGE_DELETE';
    public const PIN = 'MESSAGE_PIN';

    private PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PIN])) {
            return false;
        }

        // For CREATE, subject can be null
        if ($attribute === self::CREATE) {
            return true;
        }

        return $subject instanceof Message;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Message|null $message */
        $message = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($user, $message),
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($user, $message),
            self::DELETE => $this->canDelete($user, $message),
            self::PIN => $this->canPin($user, $message),
            default => false,
        };
    }

    private function canView(User $user, Message $message): bool
    {
        return $this->permissionService->hasPermission($user, 'message.view', $message);
    }

    private function canCreate(User $user): bool
    {
        return $this->permissionService->hasPermission($user, 'message.create');
    }

    private function canEdit(User $user, Message $message): bool
    {
        return $this->permissionService->hasPermission($user, 'message.edit', $message);
    }

    private function canDelete(User $user, Message $message): bool
    {
        return $this->permissionService->hasPermission($user, 'message.delete', $message);
    }

    private function canPin(User $user, Message $message): bool
    {
        return $this->permissionService->hasPermission($user, 'message.pin', $message);
    }
}
