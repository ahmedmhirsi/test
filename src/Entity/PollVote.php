<?php

namespace App\Entity;

use App\Repository\PollVoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollVoteRepository::class)]
#[ORM\Table(name: 'poll_vote')]
class PollVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_vote')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PollOption::class, inversedBy: 'votes')]
    #[ORM\JoinColumn(name: 'id_option', referencedColumnName: 'id_option', nullable: false)]
    private ?PollOption $option = null;

    // User relation removed

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $voted_at = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ip_address = null;

    public function __construct()
    {
        $this->voted_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOption(): ?PollOption
    {
        return $this->option;
    }

    public function setOption(?PollOption $option): static
    {
        $this->option = $option;
        return $this;
    }

    // User methods removed

    public function getVotedAt(): ?\DateTimeInterface
    {
        return $this->voted_at;
    }

    public function setVotedAt(\DateTimeInterface $voted_at): static
    {
        $this->voted_at = $voted_at;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ip_address;
    }

    public function setIpAddress(?string $ip_address): static
    {
        $this->ip_address = $ip_address;
        return $this;
    }
}
