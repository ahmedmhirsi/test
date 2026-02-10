<?php

namespace App\Entity;

use App\Repository\PollOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollOptionRepository::class)]
#[ORM\Table(name: 'poll_option')]
class PollOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_option')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    #[ORM\Column(type: 'integer')]
    private ?int $position = 0;

    #[ORM\ManyToOne(targetEntity: Poll::class, inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'id_poll', referencedColumnName: 'id_poll', nullable: false)]
    private ?Poll $poll = null;

    #[ORM\OneToMany(targetEntity: PollVote::class, mappedBy: 'option', cascade: ['persist', 'remove'])]
    private Collection $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(?Poll $poll): static
    {
        $this->poll = $poll;
        return $this;
    }

    /**
     * @return Collection<int, PollVote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(PollVote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setOption($this);
        }
        return $this;
    }

    public function removeVote(PollVote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            if ($vote->getOption() === $this) {
                $vote->setOption(null);
            }
        }
        return $this;
    }

    // Business methods
    public function getVoteCount(): int
    {
        return $this->votes->count();
    }

    public function getVotePercentage(): float
    {
        $totalVotes = $this->poll?->getTotalVotes() ?? 0;
        if ($totalVotes === 0) {
            return 0.0;
        }
        return ($this->getVoteCount() / $totalVotes) * 100;
    }

    public function __toString(): string
    {
        return $this->text ?? '';
    }
}
