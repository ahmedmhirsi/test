<?php

namespace App\Entity;

use App\Repository\MarketingBudgetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingBudgetRepository::class)]
#[ORM\Table(name: 'marketing_budget')]
class MarketingBudget
{
    public const CURRENCY_TND = 'TND';
    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_USD = 'USD';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingCampaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingChannel $channel = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $plannedAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $actualAmount = '0.00';

    #[ORM\Column(length: 3)]
    private ?string $currency = self::CURRENCY_TND;

    #[ORM\Column(length: 7)]
    private ?string $periodMonth = null; // Format: 2026-02

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->periodMonth = (new \DateTime())->format('Y-m');
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

    public function getPlannedAmount(): ?string
    {
        return $this->plannedAmount;
    }

    public function setPlannedAmount(string $plannedAmount): static
    {
        $this->plannedAmount = $plannedAmount;
        return $this;
    }

    public function getActualAmount(): ?string
    {
        return $this->actualAmount;
    }

    public function setActualAmount(string $actualAmount): static
    {
        $this->actualAmount = $actualAmount;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function getPeriodMonth(): ?string
    {
        return $this->periodMonth;
    }

    public function setPeriodMonth(string $periodMonth): static
    {
        $this->periodMonth = $periodMonth;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getBudgetVariance(): float
    {
        $planned = (float) $this->plannedAmount;
        $actual = (float) $this->actualAmount;
        return $actual - $planned;
    }

    public function getBudgetVariancePercent(): float
    {
        $planned = (float) $this->plannedAmount;
        if ($planned == 0) {
            return 0;
        }
        return (($this->getBudgetVariance()) / $planned) * 100;
    }

    public function isOverBudget(): bool
    {
        return $this->getBudgetVariance() > 0;
    }
}
