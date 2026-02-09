<?php

namespace App\Entity;

use App\Repository\PollRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollRepository::class)]
#[ORM\Table(name: 'poll')]
class Poll
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_poll')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $question = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closed_at = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'Active'; // Active, Closed

    #[ORM\Column(type: 'boolean')]
    private ?bool $allow_multiple = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $anonymous = false;

    #[ORM\ManyToOne(targetEntity: Meeting::class)]
    #[ORM\JoinColumn(name: 'id_meeting', referencedColumnName: 'id_meeting', nullable: true)]
    private ?Meeting $meeting = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id_user', nullable: false)]
    private ?User $createdBy = null;

    #[ORM\OneToMany(targetEntity: PollOption::class, mappedBy: 'poll', cascade: ['persist', 'remove'])]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->created_at = new \DateTime();
        $this->status = 'Active';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closed_at;
    }

    public function setClosedAt(?\DateTimeInterface $closed_at): static
    {
        $this->closed_at = $closed_at;
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

    public function isAllowMultiple(): ?bool
    {
        return $this->allow_multiple;
    }

    public function setAllowMultiple(bool $allow_multiple): static
    {
        $this->allow_multiple = $allow_multiple;
        return $this;
    }

    public function isAnonymous(): ?bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): static
    {
        $this->anonymous = $anonymous;
        return $this;
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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return Collection<int, PollOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(PollOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setPoll($this);
        }
        return $this;
    }

    public function removeOption(PollOption $option): static
    {
        if ($this->options->removeElement($option)) {
            if ($option->getPoll() === $this) {
                $option->setPoll(null);
            }
        }
        return $this;
    }

    // Business methods
    public function closePoll(): void
    {
        $this->status = 'Closed';
        $this->closed_at = new \DateTime();
    }

    public function getTotalVotes(): int
    {
        $total = 0;
        foreach ($this->options as $option) {
            $total += $option->getVoteCount();
        }
        return $total;
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function __toString(): string
    {
        return $this->question ?? '';
    }
}
