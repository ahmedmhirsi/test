<?php

namespace App\Entity;

use App\Repository\OffreEmploiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OffreEmploiRepository::class)]
class OffreEmploi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le poste est obligatoire")]
    #[Assert\Length(min: 5, minMessage: "Le titre du poste doit faire au moins {{ limit }} caractères")]
    private ?string $poste = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(min: 20, minMessage: "La description doit être détaillée (min {{ limit }} caractères)")]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON)]
    private array $competencesRequises = [];

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le salaire doit être un nombre positif")]
    private ?float $salaireMin = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(propertyPath: "salaireMin", message: "Le salaire maximum doit être supérieur ou égal au salaire minimum")]
    private ?float $salaireMax = null;

    #[ORM\Column(length: 50)]
    private ?string $typeContrat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datePublication = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'offreEmploi', orphanRemoval: true)]
    private Collection $candidatures;

    #[ORM\ManyToMany(targetEntity: Formation::class)]
    private Collection $formations;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
        $this->formations = new ArrayCollection();
        $this->datePublication = new \DateTime();
        $this->statut = 'Active';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(string $poste): static
    {
        $this->poste = $poste;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCompetencesRequises(): array
    {
        return $this->competencesRequises;
    }

    public function setCompetencesRequises(array $competencesRequises): static
    {
        $this->competencesRequises = $competencesRequises;

        return $this;
    }

    public function getSalaireMin(): ?float
    {
        return $this->salaireMin;
    }

    public function setSalaireMin(?float $salaireMin): static
    {
        $this->salaireMin = $salaireMin;

        return $this;
    }

    public function getSalaireMax(): ?float
    {
        return $this->salaireMax;
    }

    public function setSalaireMax(?float $salaireMax): static
    {
        $this->salaireMax = $salaireMax;

        return $this;
    }

    public function getTypeContrat(): ?string
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(string $typeContrat): static
    {
        $this->typeContrat = $typeContrat;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeInterface $datePublication): static
    {
        $this->datePublication = $datePublication;

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

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setOffreEmploi($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getOffreEmploi() === $this) {
                $candidature->setOffreEmploi(null);
            }
        }

        return $this;
    }

    public function isActive(): bool
    {
        return $this->statut === 'Active';
    }

    public function getNbCandidatures(): int
    {
        return $this->candidatures->count();
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): static
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        $this->formations->removeElement($formation);

        return $this;
    }
}
