<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_channel')]
class UserChannel
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userChannels')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user', nullable: false)]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'userChannels')]
    #[ORM\JoinColumn(name: 'id_channel', referencedColumnName: 'id_channel', nullable: false)]
    private ?Channel $channel = null;

    #[ORM\Column(length: 20)]
    private ?string $role_in_channel = 'Viewer'; // Viewer, Moderator

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $joined_at = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $can_invite = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $can_manage_messages = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $can_create_meetings = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $can_pin_messages = false;

    public function __construct()
    {
        $this->role_in_channel = 'Viewer';
        $this->joined_at = new \DateTime();
        $this->can_invite = false;
        $this->can_manage_messages = false;
        $this->can_create_meetings = false;
        $this->can_pin_messages = false;
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

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function getRoleInChannel(): ?string
    {
        return $this->role_in_channel;
    }

    public function setRoleInChannel(string $role_in_channel): static
    {
        $this->role_in_channel = $role_in_channel;
        return $this;
    }

    public function getJoinedAt(): ?\DateTimeInterface
    {
        return $this->joined_at;
    }

    public function setJoinedAt(\DateTimeInterface $joined_at): static
    {
        $this->joined_at = $joined_at;
        return $this;
    }

    public function canInvite(): ?bool
    {
        return $this->can_invite;
    }

    public function setCanInvite(bool $can_invite): static
    {
        $this->can_invite = $can_invite;
        return $this;
    }

    public function canManageMessages(): ?bool
    {
        return $this->can_manage_messages;
    }

    public function setCanManageMessages(bool $can_manage_messages): static
    {
        $this->can_manage_messages = $can_manage_messages;
        return $this;
    }

    public function canCreateMeetings(): ?bool
    {
        return $this->can_create_meetings;
    }

    public function setCanCreateMeetings(bool $can_create_meetings): static
    {
        $this->can_create_meetings = $can_create_meetings;
        return $this;
    }

    public function canPinMessages(): ?bool
    {
        return $this->can_pin_messages;
    }

    public function setCanPinMessages(bool $can_pin_messages): static
    {
        $this->can_pin_messages = $can_pin_messages;
        return $this;
    }

    public function isModerator(): bool
    {
        return $this->role_in_channel === 'Moderator';
    }
}
