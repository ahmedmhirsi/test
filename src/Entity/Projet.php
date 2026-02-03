<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(nullable: true)]
    private ?float $budget = null;

    #[ORM\Column(length: 30)]
    private ?string $statut = null;

    #[ORM\Column(length: 20)]
    private ?string $priorite = null;

    // NEW: External User reference for project manager
    #[ORM\Column(nullable: true)]
    private ?int $managerId = null;

    /**
     * @var Collection<int, Sprint>
     */
    #[ORM\OneToMany(targetEntity: Sprint::class, mappedBy: 'projet')]
    private Collection $sprints;

    /**
     * @var Collection<int, Jalon>
     */
    #[ORM\OneToMany(targetEntity: Jalon::class, mappedBy: 'projet')]
    private Collection $jalons;

    public function __construct()
    {
        $this->sprints = new ArrayCollection();
        $this->jalons = new ArrayCollection();
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

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): static
    {
        $this->budget = $budget;

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

    public function getManagerId(): ?int
    {
        return $this->managerId;
    }

    public function setManagerId(?int $managerId): static
    {
        $this->managerId = $managerId;

        return $this;
    }

    /**
     * @return Collection<int, Sprint>
     */
    public function getSprints(): Collection
    {
        return $this->sprints;
    }

    public function addSprint(Sprint $sprint): static
    {
        if (!$this->sprints->contains($sprint)) {
            $this->sprints->add($sprint);
            $sprint->setProjet($this);
        }

        return $this;
    }

    public function removeSprint(Sprint $sprint): static
    {
        if ($this->sprints->removeElement($sprint)) {
            // set the owning side to null (unless already changed)
            if ($sprint->getProjet() === $this) {
                $sprint->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Jalon>
     */
    public function getJalons(): Collection
    {
        return $this->jalons;
    }

    public function addJalon(Jalon $jalon): static
    {
        if (!$this->jalons->contains($jalon)) {
            $this->jalons->add($jalon);
            $jalon->setProjet($this);
        }

        return $this;
    }

    public function removeJalon(Jalon $jalon): static
    {
        if ($this->jalons->removeElement($jalon)) {
            // set the owning side to null (unless already changed)
            if ($jalon->getProjet() === $this) {
                $jalon->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * Get total number of tasks across all sprints
     */
    public function getTotalTasksCount(): int
    {
        $count = 0;
        foreach ($this->sprints as $sprint) {
            $count += $sprint->getTaches()->count();
        }
        return $count;
    }

    /**
     * Get number of completed tasks across all sprints
     */
    public function getCompletedTasksCount(): int
    {
        $count = 0;
        foreach ($this->sprints as $sprint) {
            foreach ($sprint->getTaches() as $tache) {
                if ($tache->isCompleted()) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Get project completion percentage
     */
    public function getCompletionPercentage(): int
    {
        $total = $this->getTotalTasksCount();
        if ($total === 0) {
            return 0;
        }
        return (int) round(($this->getCompletedTasksCount() / $total) * 100);
    }

    /**
     * Get validated milestones count
     */
    public function getValidatedMilestonesCount(): int
    {
        return $this->jalons->filter(fn($j) => $j->isValidated())->count();
    }
}
