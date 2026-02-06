<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le message ne peut pas être vide')]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateReponse = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'auteur ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom de l'auteur doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom de l'auteur ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $auteur = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le type d'auteur ne peut pas être vide")]
    #[Assert\Choice(
        choices: ['admin', 'client'],
        message: "Le type d'auteur doit être 'admin' ou 'client'"
    )]
    private ?string $auteurType = null;

    #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'La réponse doit être associée à une réclamation')]
    private ?Reclamation $reclamation = null;

    public function __construct()
    {
        $this->dateReponse = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message ?? '';

        return $this;
    }

    public function getDateReponse(): ?\DateTimeInterface
    {
        return $this->dateReponse;
    }

    public function setDateReponse(\DateTimeInterface $dateReponse): static
    {
        $this->dateReponse = $dateReponse;

        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(?string $auteur): static
    {
        $this->auteur = $auteur ?? '';

        return $this;
    }

    public function getAuteurType(): ?string
    {
        return $this->auteurType;
    }

    public function setAuteurType(?string $auteurType): static
    {
        $this->auteurType = $auteurType ?? 'client';

        return $this;
    }

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): static
    {
        $this->reclamation = $reclamation;

        return $this;
    }
}
