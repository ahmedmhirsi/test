<?php

namespace App\Entity;

use App\Repository\MarketingCampaignChannelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingCampaignChannelRepository::class)]
#[ORM\Table(name: 'marketing_campaign_channel')]
class MarketingCampaignChannel
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'campaignChannels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingCampaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'campaignChannels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingChannel $channel = null;

    #[ORM\Column]
    private ?int $expectedLeads = 0;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_ACTIVE;

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

    public function getExpectedLeads(): ?int
    {
        return $this->expectedLeads;
    }

    public function setExpectedLeads(int $expectedLeads): static
    {
        $this->expectedLeads = $expectedLeads;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
