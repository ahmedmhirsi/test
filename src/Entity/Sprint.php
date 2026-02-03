<?php

namespace App\Entity;

use App\Repository\SprintRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SprintRepository::class)]
class Sprint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(nullable: true)]
    private ?float $objectifVelocite = null;

    #[ORM\Column(nullable: true)]
    private ?float $velociteReelle = null;

    #[ORM\Column(length: 30)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'sprints')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Projet $projet = null;

    /**
     * @var Collection<int, Tache>
     */
    #[ORM\OneToMany(targetEntity: Tache::class, mappedBy: 'sprint')]
    private Collection $taches;

    /**
     * @var Collection<int, Jalon>
     */
    #[ORM\OneToMany(targetEntity: Jalon::class, mappedBy: 'sprint')]
    private Collection $jalons;

    public function __construct()
    {
        $this->taches = new ArrayCollection();
        $this->jalons = new ArrayCollection();
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

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getObjectifVelocite(): ?float
    {
        return $this->objectifVelocite;
    }

    public function setObjectifVelocite(?float $objectifVelocite): static
    {
        $this->objectifVelocite = $objectifVelocite;

        return $this;
    }

    public function getVelociteReelle(): ?float
    {
        return $this->velociteReelle;
    }

    public function setVelociteReelle(?float $velociteReelle): static
    {
        $this->velociteReelle = $velociteReelle;

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

    public function addTach(Tache $tach): static
    {
        if (!$this->taches->contains($tach)) {
            $this->taches->add($tach);
            $tach->setSprint($this);
        }

        return $this;
    }

    public function removeTach(Tache $tach): static
    {
        if ($this->taches->removeElement($tach)) {
            // set the owning side to null (unless already changed)
            if ($tach->getSprint() === $this) {
                $tach->setSprint(null);
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
            $jalon->setSprint($this);
        }

        return $this;
    }

    public function removeJalon(Jalon $jalon): static
    {
        if ($this->jalons->removeElement($jalon)) {
            // set the owning side to null (unless already changed)
            if ($jalon->getSprint() === $this) {
                $jalon->setSprint(null);
            }
        }

        return $this;
    }
}
