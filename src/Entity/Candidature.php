<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDepot = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cvPath = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lettreMotivation = null;

    #[ORM\Column(nullable: true)]
    private ?float $scoreMatchingIA = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du candidat est obligatoire")]
    #[Assert\Length(min: 2, minMessage: "Le nom est trop court")]
    private ?string $nomCandidat = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas valide")]
    private ?string $emailCandidat = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OffreEmploi $offreEmploi = null;

    public function __construct()
    {
        $this->dateDepot = new \DateTime();
        $this->statut = 'En attente';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDepot(): ?\DateTimeInterface
    {
        return $this->dateDepot;
    }

    public function setDateDepot(\DateTimeInterface $dateDepot): static
    {
        $this->dateDepot = $dateDepot;

        return $this;
    }

    public function getCvPath(): ?string
    {
        return $this->cvPath;
    }

    public function setCvPath(?string $cvPath): static
    {
        $this->cvPath = $cvPath;

        return $this;
    }

    public function getLettreMotivation(): ?string
    {
        return $this->lettreMotivation;
    }

    public function setLettreMotivation(?string $lettreMotivation): static
    {
        $this->lettreMotivation = $lettreMotivation;

        return $this;
    }

    public function getScoreMatchingIA(): ?float
    {
        return $this->scoreMatchingIA;
    }

    public function setScoreMatchingIA(?float $scoreMatchingIA): static
    {
        $this->scoreMatchingIA = $scoreMatchingIA;

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

    public function getNomCandidat(): ?string
    {
        return $this->nomCandidat;
    }

    public function setNomCandidat(string $nomCandidat): static
    {
        $this->nomCandidat = $nomCandidat;

        return $this;
    }

    public function getEmailCandidat(): ?string
    {
        return $this->emailCandidat;
    }

    public function setEmailCandidat(string $emailCandidat): static
    {
        $this->emailCandidat = $emailCandidat;

        return $this;
    }

    public function getOffreEmploi(): ?OffreEmploi
    {
        return $this->offreEmploi;
    }

    public function setOffreEmploi(?OffreEmploi $offreEmploi): static
    {
        $this->offreEmploi = $offreEmploi;

        return $this;
    }

    public function calculerCompatibilite(): float
    {
        // Mock implementation
        return $this->scoreMatchingIA ?? 0.0;
    }

    public function isRecommandee(): bool
    {
        return ($this->scoreMatchingIA ?? 0) > 80.0;
    }
}
