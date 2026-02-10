<?php

namespace App\Entity;

use App\Repository\JournalTempsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JournalTempsRepository::class)]
class JournalTemps
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date est obligatoire")]
    #[Assert\LessThanOrEqual("today", message: "Vous ne pouvez pas saisir de temps dans le futur")]
    private ?\DateTime $date = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive(message: "La durée doit être supérieure à 0")]
    private ?int $duree = null; // Duration in minutes

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    // User relation
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'journauxTemps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tache $tache = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

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

    /**
     * Get duration formatted as hours and minutes
     */
    public function getDureeFormatted(): string
    {
        $hours = floor($this->duree / 60);
        $minutes = $this->duree % 60;

        if ($hours > 0 && $minutes > 0) {
            return sprintf('%dh %dmin', $hours, $minutes);
        } elseif ($hours > 0) {
            return sprintf('%dh', $hours);
        } else {
            return sprintf('%dmin', $minutes);
        }
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

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

    public function getTache(): ?Tache
    {
        return $this->tache;
    }

    public function setTache(?Tache $tache): static
    {
        $this->tache = $tache;

        return $this;
    }
}
