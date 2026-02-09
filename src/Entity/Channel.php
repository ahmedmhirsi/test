<?php

namespace App\Entity;

use App\Repository\ChannelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChannelRepository::class)]
#[ORM\Table(name: 'channel')]
class Channel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_channel')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom du channel est obligatoire.")]
    #[Assert\Length(min: 3, max: 100, minMessage: "Le nom doit faire au moins {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['Message', 'Vocal'], message: "Type invalide.")]
    private ?string $type = null; // Message, Vocal

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['Actif', 'Inactif'], message: "Statut invalide.")]
    private ?string $statut = 'Actif'; // Actif, Inactif

    #[ORM\Column(type: 'boolean')]
    private ?bool $isLocked = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive(message: "Le nombre de participants doit être positif.")]
    private ?int $max_participants = null;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'channel', cascade: ['remove'])]
    private Collection $messages;

    #[ORM\OneToMany(targetEntity: UserChannel::class, mappedBy: 'channel', cascade: ['remove'])]
    private Collection $userChannels;

    #[ORM\OneToMany(targetEntity: Meeting::class, mappedBy: 'channelVocal')]
    private Collection $meetingsAsVocal;

    #[ORM\OneToMany(targetEntity: Meeting::class, mappedBy: 'channelMessage')]
    private Collection $meetingsAsMessage;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->userChannels = new ArrayCollection();
        $this->meetingsAsVocal = new ArrayCollection();
        $this->meetingsAsMessage = new ArrayCollection();
        $this->statut = 'Actif';
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->max_participants;
    }

    public function setMaxParticipants(?int $max_participants): static
    {
        $this->max_participants = $max_participants;
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
            $message->setChannel($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getChannel() === $this) {
                $message->setChannel(null);
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
            $userChannel->setChannel($this);
        }
        return $this;
    }

    public function removeUserChannel(UserChannel $userChannel): static
    {
        if ($this->userChannels->removeElement($userChannel)) {
            if ($userChannel->getChannel() === $this) {
                $userChannel->setChannel(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Meeting>
     */
    public function getMeetingsAsVocal(): Collection
    {
        return $this->meetingsAsVocal;
    }

    /**
     * @return Collection<int, Meeting>
     */
    public function getMeetingsAsMessage(): Collection
    {
        return $this->meetingsAsMessage;
    }

    // Business methods
    public function addUser(): void
    {
        // Logic to add a user to the channel
    }

    public function removeUser(): void
    {
        // Logic to remove a user from the channel
    }

    public function isLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): static
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    public function lockChannel(): void
    {
        $this->isLocked = true;
        // Optionally update statut too if desired, but isLocked is now the primary flag
    }

    public function unlockChannel(): void
    {
        $this->isLocked = false;
    }

    public function getParticipantCount(): int
    {
        return $this->userChannels->count();
    }

    public function getMessageCount(): int
    {
        return $this->messages->count();
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }

    public function addMeetingsAsVocal(Meeting $meetingsAsVocal): static
    {
        if (!$this->meetingsAsVocal->contains($meetingsAsVocal)) {
            $this->meetingsAsVocal->add($meetingsAsVocal);
            $meetingsAsVocal->setChannelVocal($this);
        }

        return $this;
    }

    public function removeMeetingsAsVocal(Meeting $meetingsAsVocal): static
    {
        if ($this->meetingsAsVocal->removeElement($meetingsAsVocal)) {
            // set the owning side to null (unless already changed)
            if ($meetingsAsVocal->getChannelVocal() === $this) {
                $meetingsAsVocal->setChannelVocal(null);
            }
        }

        return $this;
    }

    public function addMeetingsAsMessage(Meeting $meetingsAsMessage): static
    {
        if (!$this->meetingsAsMessage->contains($meetingsAsMessage)) {
            $this->meetingsAsMessage->add($meetingsAsMessage);
            $meetingsAsMessage->setChannelMessage($this);
        }

        return $this;
    }

    public function removeMeetingsAsMessage(Meeting $meetingsAsMessage): static
    {
        if ($this->meetingsAsMessage->removeElement($meetingsAsMessage)) {
            // set the owning side to null (unless already changed)
            if ($meetingsAsMessage->getChannelMessage() === $this) {
                $meetingsAsMessage->setChannelMessage(null);
            }
        }

        return $this;
    }
}
