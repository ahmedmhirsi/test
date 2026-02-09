<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\Index(name: 'idx_user_points', columns: ['points'])]
#[Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_user')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le nom doit faire au moins {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20, maxMessage: "Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Regex(pattern: '/^\+?[0-9\s]+$/', message: "Le numéro de téléphone n'est pas valide.")]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 20)]
    private ?string $role = null; // Admin, ProjectManager, Member

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 6, minMessage: "Le mot de passe doit faire au moins {{ limit }} caractères.")]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $statut_actif = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastSeenAt = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['Active', 'AFK'], message: "Statut invalide.")]
    private ?string $statut_channel = 'Active'; // Active, AFK

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['Active', 'AFK', 'Offline'], message: "Statut invalide.")]
    private ?string $status = 'Offline'; // Active, AFK, Offline

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $messages;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $notifications;

    #[ORM\OneToMany(targetEntity: UserChannel::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $userChannels;

    #[ORM\OneToMany(targetEntity: MeetingUser::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $meetingUsers;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_role')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user')]
    #[ORM\InverseJoinColumn(name: 'id_role', referencedColumnName: 'id_role')]
    private Collection $customRoles;

    #[ORM\OneToMany(targetEntity: UserPermission::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $userPermissions;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $points = 0;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserBadge::class, orphanRemoval: true)]
    private Collection $userBadges;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->userChannels = new ArrayCollection();
        $this->meetingUsers = new ArrayCollection();
        $this->customRoles = new ArrayCollection();
        $this->userPermissions = new ArrayCollection();
        $this->userBadges = new ArrayCollection();
        $this->statut_actif = true;
        $this->statut_channel = 'Active';
        $this->points = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function isStatutActif(): ?bool
    {
        return $this->statut_actif;
    }

    public function setStatutActif(bool $statut_actif): static
    {
        $this->statut_actif = $statut_actif;
        return $this;
    }

    public function getStatutChannel(): ?string
    {
        return $this->statut_channel;
    }

    public function setStatutChannel(string $statut_channel): static
    {
        $this->statut_channel = $statut_channel;
        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(?\DateTimeInterface $lastSeenAt): static
    {
        $this->lastSeenAt = $lastSeenAt;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setUser($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, UserChannel>
     */
    public function getUserChannels(): Collection
    {
        return $this->userChannels;
    }

    public function addUserChannel(UserChannel $userChannel): static
    {
        if (!$this->userChannels->contains($userChannel)) {
            $this->userChannels->add($userChannel);
            $userChannel->setUser($this);
        }
        return $this;
    }

    public function removeUserChannel(UserChannel $userChannel): static
    {
        if ($this->userChannels->removeElement($userChannel)) {
            if ($userChannel->getUser() === $this) {
                $userChannel->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, MeetingUser>
     */
    public function getMeetingUsers(): Collection
    {
        return $this->meetingUsers;
    }

    public function addMeetingUser(MeetingUser $meetingUser): static
    {
        if (!$this->meetingUsers->contains($meetingUser)) {
            $this->meetingUsers->add($meetingUser);
            $meetingUser->setUser($this);
        }
        return $this;
    }

    public function removeMeetingUser(MeetingUser $meetingUser): static
    {
        if ($this->meetingUsers->removeElement($meetingUser)) {
            if ($meetingUser->getUser() === $this) {
                $meetingUser->setUser(null);
            }
        }
        return $this;
    }

    // Business methods
    public function sendMessage(): void
    {
        // Logic to send a message
    }

    public function joinChannel(): void
    {
        // Logic to join a channel
    }

    public function leaveChannel(): void
    {
        // Logic to leave a channel
    }

    public function moveToAFK(): void
    {
        $this->statut_channel = 'AFK';
    }

    /**
     * @return Collection<int, Role>
     */
    public function getCustomRoles(): Collection
    {
        return $this->customRoles;
    }

    public function addCustomRole(Role $role): static
    {
        if (!$this->customRoles->contains($role)) {
            $this->customRoles->add($role);
        }
        return $this;
    }

    public function removeCustomRole(Role $role): static
    {
        $this->customRoles->removeElement($role);
        return $this;
    }

    public function hasCustomRole(string $roleName): bool
    {
        foreach ($this->customRoles as $role) {
            if ($role->getName() === $roleName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Collection<int, UserPermission>
     */
    public function getUserPermissions(): Collection
    {
        return $this->userPermissions;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;
        return $this;
    }

    /**
     * @return Collection<int, UserBadge>
     */
    public function getUserBadges(): Collection
    {
        return $this->userBadges;
    }

    public function addUserBadge(UserBadge $userBadge): static
    {
        if (!$this->userBadges->contains($userBadge)) {
            $this->userBadges->add($userBadge);
            $userBadge->setUser($this);
        }
        return $this;
    }

    public function removeUserBadge(UserBadge $userBadge): static
    {
        if ($this->userBadges->removeElement($userBadge)) {
            // set the owning side to null (unless already changed)
            if ($userBadge->getUser() === $this) {
                $userBadge->setUser(null);
            }
        }
        return $this;
    }

    public function addUserPermission(UserPermission $userPermission): static
    {
        if (!$this->userPermissions->contains($userPermission)) {
            $this->userPermissions->add($userPermission);
            $userPermission->setUser($this);
        }
        return $this;
    }

    public function removeUserPermission(UserPermission $userPermission): static
    {
        if ($this->userPermissions->removeElement($userPermission)) {
            if ($userPermission->getUser() === $this) {
                $userPermission->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        // Convert legacy role string to Symfony roles array
        $roles = ['ROLE_USER'];
        
        if ($this->role) {
            $roles[] = 'ROLE_' . strtoupper($this->role); // e.g. ROLE_ADMIN
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        // This is required by UserInterface but we rely on the single role field for business logic
        // We could map it back, but for now we leave it simple.
        // In a real app we might want to store JSON roles.
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Check if user is an admin (global role)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    /**
     * Check if user is a project manager (global role)
     */
    public function isProjectManager(): bool
    {
        return $this->role === 'ProjectManager';
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
