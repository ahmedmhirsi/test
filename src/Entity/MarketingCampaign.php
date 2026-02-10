<?php

namespace App\Entity;

use App\Repository\MarketingCampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingCampaignRepository::class)]
#[ORM\Table(name: 'marketing_campaign')]
#[ORM\HasLifecycleCallbacks]
class MarketingCampaign
{
    public const STATUS_PLANNED = 'planned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objective = null;

    #[ORM\Column]
    private ?int $targetLeads = 0;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_PLANNED;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $createdBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, MarketingCampaignChannel>
     */
    #[ORM\OneToMany(targetEntity: MarketingCampaignChannel::class, mappedBy: 'campaign', orphanRemoval: true)]
    private Collection $campaignChannels;

    /**
     * @var Collection<int, MarketingBudget>
     */
    #[ORM\OneToMany(targetEntity: MarketingBudget::class, mappedBy: 'campaign', orphanRemoval: true)]
    private Collection $budgets;

    /**
     * @var Collection<int, MarketingMessage>
     */
    #[ORM\OneToMany(targetEntity: MarketingMessage::class, mappedBy: 'campaign', orphanRemoval: true)]
    private Collection $messages;

    /**
     * @var Collection<int, MarketingLead>
     */
    #[ORM\OneToMany(targetEntity: MarketingLead::class, mappedBy: 'campaign', orphanRemoval: true)]
    private Collection $leads;

    /**
     * @var Collection<int, MarketingPerformance>
     */
    #[ORM\OneToMany(targetEntity: MarketingPerformance::class, mappedBy: 'campaign', orphanRemoval: true)]
    private Collection $performances;

    public function __construct()
    {
        $this->campaignChannels = new ArrayCollection();
        $this->budgets = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->leads = new ArrayCollection();
        $this->performances = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
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

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(?string $objective): static
    {
        $this->objective = $objective;
        return $this;
    }

    public function getTargetLeads(): ?int
    {
        return $this->targetLeads;
    }

    public function setTargetLeads(int $targetLeads): static
    {
        $this->targetLeads = $targetLeads;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
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

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, MarketingCampaignChannel>
     */
    public function getCampaignChannels(): Collection
    {
        return $this->campaignChannels;
    }

    public function addCampaignChannel(MarketingCampaignChannel $campaignChannel): static
    {
        if (!$this->campaignChannels->contains($campaignChannel)) {
            $this->campaignChannels->add($campaignChannel);
            $campaignChannel->setCampaign($this);
        }
        return $this;
    }

    public function removeCampaignChannel(MarketingCampaignChannel $campaignChannel): static
    {
        if ($this->campaignChannels->removeElement($campaignChannel)) {
            if ($campaignChannel->getCampaign() === $this) {
                $campaignChannel->setCampaign(null);
            }
        }
        return $this;
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

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PLANNED => 'Planned',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Unknown'
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PLANNED => 'blue',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_PAUSED => 'amber',
            self::STATUS_COMPLETED => 'gray',
            default => 'gray'
        };
    }
}
