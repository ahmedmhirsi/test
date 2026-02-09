<?php

namespace App\Entity;

use App\Repository\UserPermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPermissionRepository::class)]
#[ORM\Table(name: 'user_permission')]
class UserPermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userPermissions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id_user', nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Permission::class, inversedBy: 'userPermissions')]
    #[ORM\JoinColumn(name: 'permission_id', referencedColumnName: 'id_permission', nullable: false)]
    private ?Permission $permission = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $resource_type = null; // Channel, Meeting, Message, etc. (null = global)

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $resource_id = null; // ID of specific resource (null = all resources of type)

    #[ORM\Column(type: 'boolean')]
    private ?bool $granted = true; // true = grant, false = deny

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $granted_at = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'granted_by', referencedColumnName: 'id_user', nullable: true)]
    private ?User $granted_by = null;

    public function __construct()
    {
        $this->granted_at = new \DateTime();
        $this->granted = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPermission(): ?Permission
    {
        return $this->permission;
    }

    public function setPermission(?Permission $permission): static
    {
        $this->permission = $permission;
        return $this;
    }

    public function getResourceType(): ?string
    {
        return $this->resource_type;
    }

    public function setResourceType(?string $resource_type): static
    {
        $this->resource_type = $resource_type;
        return $this;
    }

    public function getResourceId(): ?int
    {
        return $this->resource_id;
    }

    public function setResourceId(?int $resource_id): static
    {
        $this->resource_id = $resource_id;
        return $this;
    }

    public function isGranted(): ?bool
    {
        return $this->granted;
    }

    public function setGranted(bool $granted): static
    {
        $this->granted = $granted;
        return $this;
    }

    public function getGrantedAt(): ?\DateTimeInterface
    {
        return $this->granted_at;
    }

    public function setGrantedAt(\DateTimeInterface $granted_at): static
    {
        $this->granted_at = $granted_at;
        return $this;
    }

    public function getGrantedBy(): ?User
    {
        return $this->granted_by;
    }

    public function setGrantedBy(?User $granted_by): static
    {
        $this->granted_by = $granted_by;
        return $this;
    }

    /**
     * Check if this permission applies to a specific resource
     */
    public function appliesTo(?string $resourceType, ?int $resourceId): bool
    {
        // Global permission (no resource specified)
        if ($this->resource_type === null && $this->resource_id === null) {
            return true;
        }

        // Resource type specific (all resources of this type)
        if ($this->resource_type === $resourceType && $this->resource_id === null) {
            return true;
        }

        // Specific resource
        if ($this->resource_type === $resourceType && $this->resource_id === $resourceId) {
            return true;
        }

        return false;
    }
}
