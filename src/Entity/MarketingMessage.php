<?php

namespace App\Entity;

use App\Repository\MarketingMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingMessageRepository::class)]
#[ORM\Table(name: 'marketing_message')]
class MarketingMessage
{
    public const VARIANT_A = 'A';
    public const VARIANT_B = 'B';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingCampaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingChannel $channel = null;

    #[ORM\Column(length: 1)]
    private ?string $variant = self::VARIANT_A;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, MarketingLead>
     */
    #[ORM\OneToMany(targetEntity: MarketingLead::class, mappedBy: 'message')]
    private Collection $leads;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->leads = new ArrayCollection();
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

    public function getVariant(): ?string
    {
        return $this->variant;
    }

    public function setVariant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, MarketingLead>
     */
    public function getLeads(): Collection
    {
        return $this->leads;
    }

    public function getLeadCount(): int
    {
        return $this->leads->count();
    }

    public function getConvertedLeadCount(): int
    {
        return $this->leads->filter(fn($lead) => $lead->getStatus() === MarketingLead::STATUS_CONVERTED)->count();
    }

    public function getConversionRate(): float
    {
        $total = $this->getLeadCount();
        if ($total === 0) {
            return 0;
        }
        return ($this->getConvertedLeadCount() / $total) * 100;
    }
}
