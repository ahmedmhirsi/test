<?php

namespace App\Entity;

use App\Repository\JalonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JalonRepository::class)]
class Jalon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateEcheance = null;

    #[ORM\Column(length: 30)]
    #[Assert\Choice(choices: ['planifie', 'en_cours', 'atteint', 'retarde'], message: "Le statut doit être valide")]
    private ?string $statut = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['basse', 'moyenne', 'haute', 'critique'], message: "La priorité doit être valide")]
    private ?string $priorite = null;

    #[ORM\ManyToOne(inversedBy: 'jalons')]
    private ?Sprint $sprint = null;

    #[ORM\ManyToOne(inversedBy: 'jalons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Projet $projet = null;

    // NEW: Tasks linked to this milestone for validation
    /**
     * @var Collection<int, Tache>
     */
    #[ORM\OneToMany(targetEntity: Tache::class, mappedBy: 'jalon')]
    private Collection $taches;

    public function __construct()
    {
        $this->taches = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateEcheance(): ?\DateTime
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(\DateTime $dateEcheance): static
    {
        $this->dateEcheance = $dateEcheance;

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

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(string $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getSprint(): ?Sprint
    {
        return $this->sprint;
    }

    public function setSprint(?Sprint $sprint): static
    {
        $this->sprint = $sprint;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): static
    {
        $this->projet = $projet;

        return $this;
    }

    /**
     * @return Collection<int, Tache>
     */
    public function getTaches(): Collection
    {
        return $this->taches;
    }

    public function addTache(Tache $tache): static
    {
        if (!$this->taches->contains($tache)) {
            $this->taches->add($tache);
            $tache->setJalon($this);
        }

        return $this;
    }

    public function removeTache(Tache $tache): static
    {
        if ($this->taches->removeElement($tache)) {
            // set the owning side to null (unless already changed)
            if ($tache->getJalon() === $this) {
                $tache->setJalon(null);
            }
        }

        return $this;
    }

    /**
     * Check if the milestone is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->statut === 'atteint') {
            return false;
        }
        return $this->dateEcheance < new \DateTime('today');
    }

    /**
     * Get days remaining until deadline
     */
    public function getDaysRemaining(): int
    {
        $now = new \DateTime('today');
        $diff = $now->diff($this->dateEcheance);
        return $diff->invert ? -$diff->days : $diff->days;
    }

    /**
     * Get count of completed tasks linked to this milestone
     */
    public function getCompletedTasksCount(): int
    {
        return $this->taches->filter(
            fn($t) => in_array(strtolower($t->getStatut()), ['done', 'terminé', 'termine', 'completed'])
        )->count();
    }

    /**
     * Check if milestone is validated (at least 3 completed tasks)
     */
    public function isValidated(): bool
    {
        return $this->getCompletedTasksCount() >= 3;
    }

    /**
     * Get calculated status based on task completion
     * - en_attente: 0 tasks completed
     * - en_cours: 1-2 tasks completed
     * - atteint: 3+ tasks completed
     */
    public function getCalculatedStatut(): string
    {
        $completedCount = $this->getCompletedTasksCount();

        if ($completedCount >= 3) {
            return 'atteint';
        }
        if ($completedCount > 0) {
            return 'en_cours';
        }
        return 'en_attente';
    }

    /**
     * Get progress percentage (0-100) based on 3-task rule
     */
    public function getProgressPercentage(): int
    {
        $completedCount = $this->getCompletedTasksCount();
        return min(100, (int) round(($completedCount / 3) * 100));
    }
}
