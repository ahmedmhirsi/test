<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'message')]
#[ORM\Index(name: 'idx_message_date_envoi', columns: ['date_envoi'])]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_message')]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu du message ne peut pas être vide.")]
    #[Assert\Length(
        min: 1, 
        max: 2000, 
        minMessage: "Le message doit contenir au moins {{ limit }} caractère.", 
        maxMessage: "Le message ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_envoi = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = 'Visible'; // Visible, Deleted

    #[ORM\Column(length: 20)]
    private ?string $visibility = 'All'; // All, Backoffice

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isModerated = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $authorName = 'Guest';

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(name: 'id_channel', referencedColumnName: 'id_channel', nullable: false)]
    private ?Channel $channel = null;

    #[ORM\Column(length: 20, options: ['default' => 'user'])]
    private ?string $type = 'user'; // user, ai

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachment = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $attachmentType = null;

    public function __construct()
    {
        $this->date_envoi = new \DateTime();
        $this->statut = 'Visible';
        $this->visibility = 'All';
        $this->type = 'user';
        $this->authorName = 'Guest';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->date_envoi;
    }

    public function setDateEnvoi(\DateTimeInterface $date_envoi): static
    {
        $this->date_envoi = $date_envoi;
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

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): static
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): static
    {
        $this->attachment = $attachment;
        return $this;
    }

    public function getAttachmentType(): ?string
    {
        return $this->attachmentType;
    }

    public function setAttachmentType(?string $attachmentType): static
    {
        $this->attachmentType = $attachmentType;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    // Business methods
    public function deleteMessage(): void
    {
        $this->statut = 'Deleted';
    }

    /**
     * Extract hashtags from message content
     */
    public function getHashtags(): array
    {
        preg_match_all('/#(\w+)/', $this->contenu, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Check if message contains a specific hashtag
     */
    public function hasHashtag(string $tag): bool
    {
        return in_array(strtolower($tag), array_map('strtolower', $this->getHashtags()));
    }

    public function __toString(): string
    {
        return substr($this->contenu ?? '', 0, 50) . '...';
    }
}
