<?php

namespace App\Entity;

use App\Repository\MarketingPerformanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingPerformanceRepository::class)]
#[ORM\Table(name: 'marketing_performance')]
class MarketingPerformance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'performances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingCampaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'performances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingChannel $channel = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $totalSpent = '0.00';

    #[ORM\Column]
    private ?int $totalLeads = 0;

    #[ORM\Column]
    private ?int $totalConverted = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $cac = '0.00'; // Cost of Acquisition Client

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $roi = '0.00'; // Return on Investment (percentage)

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $calculatedAt = null;

    public function __construct()
    {
        $this->calculatedAt = new \DateTime();
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

    public function getTotalSpent(): ?string
    {
        return $this->totalSpent;
    }

    public function setTotalSpent(string $totalSpent): static
    {
        $this->totalSpent = $totalSpent;
        return $this;
    }

    public function getTotalLeads(): ?int
    {
        return $this->totalLeads;
    }

    public function setTotalLeads(int $totalLeads): static
    {
        $this->totalLeads = $totalLeads;
        return $this;
    }

    public function getTotalConverted(): ?int
    {
        return $this->totalConverted;
    }

    public function setTotalConverted(int $totalConverted): static
    {
        $this->totalConverted = $totalConverted;
        return $this;
    }

    public function getCac(): ?string
    {
        return $this->cac;
    }

    public function setCac(string $cac): static
    {
        $this->cac = $cac;
        return $this;
    }

    public function getRoi(): ?string
    {
        return $this->roi;
    }

    public function setRoi(string $roi): static
    {
        $this->roi = $roi;
        return $this;
    }

    public function getCalculatedAt(): ?\DateTimeInterface
    {
        return $this->calculatedAt;
    }

    public function setCalculatedAt(\DateTimeInterface $calculatedAt): static
    {
        $this->calculatedAt = $calculatedAt;
        return $this;
    }

    /**
     * Calculate CAC (Cost per Acquisition)
     * Formula: Total Spent / Total Converted
     */
    public function calculateCac(): void
    {
        if ($this->totalConverted > 0) {
            $this->cac = number_format((float)$this->totalSpent / $this->totalConverted, 2, '.', '');
        } else {
            $this->cac = '0.00';
        }
    }

    /**
     * Get conversion rate percentage
     */
    public function getConversionRate(): float
    {
        if ($this->totalLeads === 0) {
            return 0;
        }
        return ($this->totalConverted / $this->totalLeads) * 100;
    }

    /**
     * Get cost per lead
     */
    public function getCostPerLead(): float
    {
        if ($this->totalLeads === 0) {
            return 0;
        }
        return (float)$this->totalSpent / $this->totalLeads;
    }

    /**
     * Get performance score (0-100)
     * Based on conversion rate and CAC efficiency
     */
    public function getPerformanceScore(): int
    {
        $conversionRate = $this->getConversionRate();
        $cacValue = (float)$this->cac;
        
        // Base score from conversion rate (max 50 points)
        $conversionScore = min(50, $conversionRate * 2);
        
        // CAC efficiency score (max 50 points, lower CAC = higher score)
        // Assuming a good CAC is under 500
        $cacScore = max(0, 50 - ($cacValue / 10));
        
        return (int) min(100, $conversionScore + $cacScore);
    }
}
