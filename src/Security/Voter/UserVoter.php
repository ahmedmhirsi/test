<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Voter pour contrôler les permissions granulaires sur les utilisateurs
 */
class UserVoter extends Voter
{
    public const VIEW = 'USER_VIEW';
    public const EDIT = 'USER_EDIT';
    public const DELETE = 'USER_DELETE';
    public const MANAGE_ROLES = 'USER_MANAGE_ROLES';
    public const ACTIVATE = 'USER_ACTIVATE';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::MANAGE_ROLES, self::ACTIVATE])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false;
        }

        /** @var User $targetUser */
        $targetUser = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($currentUser, $targetUser),
            self::EDIT => $this->canEdit($currentUser, $targetUser),
            self::DELETE => $this->canDelete($currentUser, $targetUser),
            self::MANAGE_ROLES => $this->canManageRoles($currentUser, $targetUser),
            self::ACTIVATE => $this->canActivate($currentUser, $targetUser),
            default => false,
        };
    }

    private function canView(User $currentUser, User $targetUser): bool
    {
        // Tout le monde peut voir son propre profil
        if ($currentUser->getId() === $targetUser->getId()) {
            return true;
        }

        // Admin et Employee peuvent voir tous les profils
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_EMPLOYEE')) {
            return true;
        }

        return false;
    }

    private function canEdit(User $currentUser, User $targetUser): bool
    {
        // Tout le monde peut modifier son propre profil
        if ($currentUser->getId() === $targetUser->getId()) {
            return true;
        }

        // Seul Admin peut modifier les autres utilisateurs
        if ($this->security->isGranted('ROLE_ADMIN')) {
            // Mais un admin ne peut pas modifier un autre admin (sauf lui-même)
            if (in_array('ROLE_ADMIN', $targetUser->getRoles()) && $currentUser->getId() !== $targetUser->getId()) {
                return false;
            }
            return true;
        }

        return false;
    }

    private function canDelete(User $currentUser, User $targetUser): bool
    {
        // Personne ne peut se supprimer soi-même
        if ($currentUser->getId() === $targetUser->getId()) {
            return false;
        }

        // Seul Admin peut supprimer des utilisateurs
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }

        // Un admin ne peut pas supprimer un autre admin
        if (in_array('ROLE_ADMIN', $targetUser->getRoles())) {
            return false;
        }

        return true;
    }

    private function canManageRoles(User $currentUser, User $targetUser): bool
    {
        // Seul Admin peut gérer les rôles
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }

        // Un admin ne peut pas modifier les rôles d'un autre admin
        if (in_array('ROLE_ADMIN', $targetUser->getRoles()) && $currentUser->getId() !== $targetUser->getId()) {
            return false;
        }

        return true;
    }

    private function canActivate(User $currentUser, User $targetUser): bool
    {
        // Admin peut activer/désactiver n'importe qui sauf un autre admin
        if ($this->security->isGranted('ROLE_ADMIN')) {
            if (in_array('ROLE_ADMIN', $targetUser->getRoles()) && $currentUser->getId() !== $targetUser->getId()) {
                return false;
            }
            return true;
        }

        // Employee peut activer/désactiver les visiteurs, candidats et clients
        if ($this->security->isGranted('ROLE_EMPLOYEE')) {
            $allowedRoles = ['ROLE_VISITEUR', 'ROLE_CANDIDAT', 'ROLE_CLIENT'];
            $targetRoles = $targetUser->getRoles();
            
            foreach ($targetRoles as $role) {
                if (!in_array($role, $allowedRoles) && $role !== 'ROLE_USER') {
                    return false;
                }
            }
            return true;
        }

        return false;
    }
}
