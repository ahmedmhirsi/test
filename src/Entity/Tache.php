<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Le titre de la tâche est obligatoire")]
    #[Assert\Length(min: 3, max: 180, minMessage: "Le titre doit faire au moins {{ limit }} caractères")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['todo', 'in_progress', 'review', 'done'], message: "Le statut doit être valide")]
    private ?string $statut = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: ['basse', 'moyenne', 'haute', 'critique'], message: "La priorité doit être valide")]
    private ?string $priorite = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'estimation de temps est obligatoire")]
    #[Assert\Positive(message: "L'estimation doit être positive")]
    private ?int $tempsEstime = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempsReel = null;

    #[ORM\ManyToOne(inversedBy: 'taches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sprint $sprint = null;

    // NEW: External User reference for task assignment
    #[ORM\Column(nullable: true)]
    private ?int $assigneeId = null;

    // NEW: Link to milestone for the 3-task validation rule
    #[ORM\ManyToOne(inversedBy: 'taches')]
    private ?Jalon $jalon = null;

    // NEW: Order for Kanban board positioning
    #[ORM\Column(nullable: true)]
    private ?int $ordre = null;

    // NEW: Due date for the task
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateEcheance = null;

    // NEW: Time log entries for this task
    /**
     * @var Collection<int, JournalTemps>
     */
    #[ORM\OneToMany(targetEntity: JournalTemps::class, mappedBy: 'tache', orphanRemoval: true)]
    private Collection $journauxTemps;

    public function __construct()
    {
        $this->journauxTemps = new ArrayCollection();
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

    public function getTempsEstime(): ?int
    {
        return $this->tempsEstime;
    }

    public function setTempsEstime(int $tempsEstime): static
    {
        $this->tempsEstime = $tempsEstime;

        return $this;
    }

    public function getTempsReel(): ?int
    {
        return $this->tempsReel;
    }

    public function setTempsReel(?int $tempsReel): static
    {
        $this->tempsReel = $tempsReel;

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

    public function getAssigneeId(): ?int
    {
        return $this->assigneeId;
    }

    public function setAssigneeId(?int $assigneeId): static
    {
        $this->assigneeId = $assigneeId;

        return $this;
    }

    public function getJalon(): ?Jalon
    {
        return $this->jalon;
    }

    public function setJalon(?Jalon $jalon): static
    {
        $this->jalon = $jalon;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getDateEcheance(): ?\DateTime
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(?\DateTime $dateEcheance): static
    {
        $this->dateEcheance = $dateEcheance;

        return $this;
    }

    /**
     * @return Collection<int, JournalTemps>
     */
    public function getJournauxTemps(): Collection
    {
        return $this->journauxTemps;
    }

    public function addJournalTemps(JournalTemps $journalTemps): static
    {
        if (!$this->journauxTemps->contains($journalTemps)) {
            $this->journauxTemps->add($journalTemps);
            $journalTemps->setTache($this);
        }

        return $this;
    }

    public function removeJournalTemps(JournalTemps $journalTemps): static
    {
        if ($this->journauxTemps->removeElement($journalTemps)) {
            // set the owning side to null (unless already changed)
            if ($journalTemps->getTache() === $this) {
                $journalTemps->setTache(null);
            }
        }

        return $this;
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        if (in_array(strtolower($this->statut), ['done', 'terminé', 'termine'])) {
            return false;
        }
        if ($this->dateEcheance === null) {
            return false;
        }
        return $this->dateEcheance < new \DateTime('today');
    }

    /**
     * Check if task is completed
     */
    public function isCompleted(): bool
    {
        return in_array(strtolower($this->statut), ['done', 'terminé', 'termine']);
    }

    /**
     * Get total logged time in minutes
     */
    public function getTotalLoggedTime(): int
    {
        $total = 0;
        foreach ($this->journauxTemps as $entry) {
            $total += $entry->getDuree() ?? 0;
        }
        return $total;
    }
}
