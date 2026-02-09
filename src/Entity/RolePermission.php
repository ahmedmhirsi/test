<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'role_permission')]
class RolePermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'rolePermissions')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id_role', nullable: false)]
    private ?Role $role = null;

    #[ORM\ManyToOne(targetEntity: Permission::class, inversedBy: 'rolePermissions')]
    #[ORM\JoinColumn(name: 'permission_id', referencedColumnName: 'id_permission', nullable: false)]
    private ?Permission $permission = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $assigned_at = null;

    public function __construct()
    {
        $this->assigned_at = new \DateTime();
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;
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

    public function getAssignedAt(): ?\DateTimeInterface
    {
        return $this->assigned_at;
    }

    public function setAssignedAt(\DateTimeInterface $assigned_at): static
    {
        $this->assigned_at = $assigned_at;
        return $this;
    }
}
