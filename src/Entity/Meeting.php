<?php

namespace App\Entity;

use App\Repository\MeetingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MeetingRepository::class)]
#[ORM\Table(name: 'meeting')]
class Meeting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_meeting')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(min: 3, max: 150, minMessage: "Le titre doit faire au moins {{ limit }} caractères.", maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    #[Assert\GreaterThan("today", message: "La date de début doit être ultérieure à aujourd'hui.")]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "La durée est obligatoire.")]
    #[Assert\Positive(message: "La durée doit être positive.")]
    private ?int $duree = null; // in minutes

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $agenda = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['Planifié', 'En cours', 'Terminé'], message: "Statut invalide.")]
    private ?string $statut = 'Planifié'; // Planifié, En cours, Terminé

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'meetingsAsVocal')]
    #[ORM\JoinColumn(name: 'id_channel_vocal', referencedColumnName: 'id_channel', nullable: true)]
    private ?Channel $channelVocal = null;

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'meetingsAsMessage')]
    #[ORM\JoinColumn(name: 'id_channel_message', referencedColumnName: 'id_channel', nullable: true)]
    private ?Channel $channelMessage = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "Le lien Google Meet n'est pas valide.")]
    #[Assert\Length(max: 255, maxMessage: "Le lien ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $google_meet_link = null;

    #[ORM\OneToMany(targetEntity: MeetingUser::class, mappedBy: 'meeting', cascade: ['persist', 'remove'])]
    private Collection $meetingUsers;

    public function __construct()
    {
        $this->meetingUsers = new ArrayCollection();
        $this->statut = 'Planifié';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    public function getAgenda(): ?string
    {
        return $this->agenda;
    }

    public function setAgenda(?string $agenda): static
    {
        $this->agenda = $agenda;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getChannelVocal(): ?Channel
    {
        return $this->channelVocal;
    }

    public function setChannelVocal(?Channel $channelVocal): static
    {
        $this->channelVocal = $channelVocal;
        return $this;
    }

    public function getChannelMessage(): ?Channel
    {
        return $this->channelMessage;
    }

    public function setChannelMessage(?Channel $channelMessage): static
    {
        $this->channelMessage = $channelMessage;
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
            $meetingUser->setMeeting($this);
        }
        return $this;
    }

    public function removeMeetingUser(MeetingUser $meetingUser): static
    {
        if ($this->meetingUsers->removeElement($meetingUser)) {
            if ($meetingUser->getMeeting() === $this) {
                $meetingUser->setMeeting(null);
            }
        }
        return $this;
    }

    // Business methods
    public function startMeeting(): void
    {
        $this->statut = 'En cours';
    }

    public function endMeeting(): void
    {
        $this->statut = 'Terminé';
    }

    public function moveUserToAFK(): void
    {
        // Logic to move user to AFK status
    }

    public function getParticipantCount(): int
    {
        return $this->meetingUsers->count();
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        if ($this->date_debut && $this->duree) {
            return (clone $this->date_debut)->modify('+' . $this->duree . ' minutes');
        }
        return null;
    }

    public function getGoogleMeetLink(): ?string
    {
        return $this->google_meet_link;
    }

    public function setGoogleMeetLink(?string $google_meet_link): static
    {
        $this->google_meet_link = $google_meet_link;
        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }
}
