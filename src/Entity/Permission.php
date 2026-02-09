<?php

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permission')]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_permission', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $resource = null; // Channel, Meeting, Message, Whiteboard, Poll, etc.

    #[ORM\Column(length: 50)]
    private ?string $action = null; // view, create, edit, delete, moderate, invite, pin, etc.

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: RolePermission::class, mappedBy: 'permission', cascade: ['remove'])]
    private Collection $rolePermissions;

    #[ORM\OneToMany(targetEntity: UserPermission::class, mappedBy: 'permission', cascade: ['remove'])]
    private Collection $userPermissions;

    public function __construct()
    {
        $this->rolePermissions = new ArrayCollection();
        $this->userPermissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(string $resource): static
    {
        $this->resource = $resource;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, RolePermission>
     */
    public function getRolePermissions(): Collection
    {
        return $this->rolePermissions;
    }

    public function addRolePermission(RolePermission $rolePermission): static
    {
        if (!$this->rolePermissions->contains($rolePermission)) {
            $this->rolePermissions->add($rolePermission);
            $rolePermission->setPermission($this);
        }
        return $this;
    }

    public function removeRolePermission(RolePermission $rolePermission): static
    {
        if ($this->rolePermissions->removeElement($rolePermission)) {
            if ($rolePermission->getPermission() === $this) {
                $rolePermission->setPermission(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, UserPermission>
     */
    public function getUserPermissions(): Collection
    {
        return $this->userPermissions;
    }

    public function addUserPermission(UserPermission $userPermission): static
    {
        if (!$this->userPermissions->contains($userPermission)) {
            $this->userPermissions->add($userPermission);
            $userPermission->setPermission($this);
        }
        return $this;
    }

    public function removeUserPermission(UserPermission $userPermission): static
    {
        if ($this->userPermissions->removeElement($userPermission)) {
            if ($userPermission->getPermission() === $this) {
                $userPermission->setPermission(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
