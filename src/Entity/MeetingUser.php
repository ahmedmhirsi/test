<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'meeting_user')]
class MeetingUser
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Meeting::class, inversedBy: 'meetingUsers')]
    #[ORM\JoinColumn(name: 'id_meeting', referencedColumnName: 'id_meeting', nullable: false)]
    private ?Meeting $meeting = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'meetingUsers')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 20)]
    private ?string $role_in_meeting = 'Participant'; // Participant, ProjectManager

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $joined_at = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $attended = false;

    public function __construct()
    {
        $this->role_in_meeting = 'Participant';
        $this->joined_at = new \DateTime();
        $this->attended = false;
    }

    public function getMeeting(): ?Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(?Meeting $meeting): static
    {
        $this->meeting = $meeting;
        return $this;
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

    public function getRoleInMeeting(): ?string
    {
        return $this->role_in_meeting;
    }

    public function setRoleInMeeting(string $role_in_meeting): static
    {
        $this->role_in_meeting = $role_in_meeting;
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

    public function isAttended(): ?bool
    {
        return $this->attended;
    }

    public function setAttended(bool $attended): static
    {
        $this->attended = $attended;
        return $this;
    }

    public function isProjectManager(): bool
    {
        return $this->role_in_meeting === 'ProjectManager';
    }
}
