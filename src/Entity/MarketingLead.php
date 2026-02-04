<?php

namespace App\Entity;

use App\Repository\MarketingLeadRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingLeadRepository::class)]
#[ORM\Table(name: 'marketing_lead')]
class MarketingLead
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_LOST = 'lost';

    public const POSITION_CEO = 'CEO';
    public const POSITION_HR_DIRECTOR = 'HR Director';
    public const POSITION_IT_DIRECTOR = 'IT Director';
    public const POSITION_CTO = 'CTO';
    public const POSITION_OTHER = 'Other';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'leads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingCampaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'leads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingChannel $channel = null;

    #[ORM\ManyToOne(inversedBy: 'leads')]
    private ?MarketingMessage $message = null;

    #[ORM\Column(length: 255)]
    private ?string $companyName = null;

    #[ORM\Column(length: 255)]
    private ?string $contactName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 100)]
    private ?string $position = self::POSITION_OTHER;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_NEW;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $sourceClickDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCampaign(): ?MarketingCampaign
    {
        return $this->campaign;
    }

    public function setCampaign(?MarketingCampaign $campaign): static
    {
        $this->campaign = $campaign;
        return $this;
    }

    public function getChannel(): ?MarketingChannel
    {
        return $this->channel;
    }

    public function setChannel(?MarketingChannel $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function getMessage(): ?MarketingMessage
    {
        return $this->message;
    }

    public function setMessage(?MarketingMessage $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): static
    {
        $this->contactName = $contactName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getSourceClickDate(): ?\DateTimeInterface
    {
        return $this->sourceClickDate;
    }

    public function setSourceClickDate(?\DateTimeInterface $sourceClickDate): static
    {
        $this->sourceClickDate = $sourceClickDate;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_QUALIFIED => 'Qualified',
            self::STATUS_CONVERTED => 'Converted',
            self::STATUS_LOST => 'Lost',
            default => 'Unknown'
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'blue',
            self::STATUS_CONTACTED => 'amber',
            self::STATUS_QUALIFIED => 'purple',
            self::STATUS_CONVERTED => 'green',
            self::STATUS_LOST => 'red',
            default => 'gray'
        };
    }

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }
}
