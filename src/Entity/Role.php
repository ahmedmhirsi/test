<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'role')]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_role', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $is_system = false; // System roles cannot be deleted

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\OneToMany(targetEntity: RolePermission::class, mappedBy: 'role', cascade: ['persist', 'remove'])]
    private Collection $rolePermissions;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'customRoles')]
    private Collection $users;

    public function __construct()
    {
        $this->rolePermissions = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->created_at = new \DateTime();
        $this->is_system = false;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isSystem(): ?bool
    {
        return $this->is_system;
    }

    public function setIsSystem(bool $is_system): static
    {
        $this->is_system = $is_system;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
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
            $rolePermission->setRole($this);
        }
        return $this;
    }

    public function removeRolePermission(RolePermission $rolePermission): static
    {
        if ($this->rolePermissions->removeElement($rolePermission)) {
            if ($rolePermission->getRole() === $this) {
                $rolePermission->setRole(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCustomRole($this);
        }
        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCustomRole($this);
        }
        return $this;
    }

    /**
     * Get all permissions for this role
     * @return array<Permission>
     */
    public function getPermissions(): array
    {
        return $this->rolePermissions->map(fn($rp) => $rp->getPermission())->toArray();
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
