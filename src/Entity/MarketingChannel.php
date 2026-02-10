<?php

namespace App\Entity;

use App\Repository\MarketingChannelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingChannelRepository::class)]
#[ORM\Table(name: 'marketing_channel')]
class MarketingChannel
{
    public const TYPE_PAID = 'paid';
    public const TYPE_ORGANIC = 'organic';
    public const TYPE_PARTNERSHIP = 'partnership';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $type = self::TYPE_PAID;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, MarketingCampaignChannel>
     */
    #[ORM\OneToMany(targetEntity: MarketingCampaignChannel::class, mappedBy: 'channel', orphanRemoval: true)]
    private Collection $campaignChannels;

    /**
     * @var Collection<int, MarketingBudget>
     */
    #[ORM\OneToMany(targetEntity: MarketingBudget::class, mappedBy: 'channel')]
    private Collection $budgets;

    /**
     * @var Collection<int, MarketingMessage>
     */
    #[ORM\OneToMany(targetEntity: MarketingMessage::class, mappedBy: 'channel')]
    private Collection $messages;

    /**
     * @var Collection<int, MarketingLead>
     */
    #[ORM\OneToMany(targetEntity: MarketingLead::class, mappedBy: 'channel')]
    private Collection $leads;

    /**
     * @var Collection<int, MarketingPerformance>
     */
    #[ORM\OneToMany(targetEntity: MarketingPerformance::class, mappedBy: 'channel')]
    private Collection $performances;

    public function __construct()
    {
        $this->campaignChannels = new ArrayCollection();
        $this->budgets = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->leads = new ArrayCollection();
        $this->performances = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, MarketingCampaignChannel>
     */
    public function getCampaignChannels(): Collection
    {
        return $this->campaignChannels;
    }

    /**
     * @return Collection<int, MarketingBudget>
     */
    public function getBudgets(): Collection
    {
        return $this->budgets;
    }

    /**
     * @return Collection<int, MarketingMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @return Collection<int, MarketingLead>
     */
    public function getLeads(): Collection
    {
        return $this->leads;
    }

    /**
     * @return Collection<int, MarketingPerformance>
     */
    public function getPerformances(): Collection
    {
        return $this->performances;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_PAID => 'Paid',
            self::TYPE_ORGANIC => 'Organic',
            self::TYPE_PARTNERSHIP => 'Partnership',
            default => 'Unknown'
        };
    }

    public function getIcon(): string
    {
        return match(strtolower($this->name ?? '')) {
            'linkedin' => 'share',
            'google ads' => 'ads_click',
            'emailing', 'cold emailing' => 'mail',
            'event', 'events' => 'event',
            default => 'campaign'
        };
    }
}
