<?php

namespace App\Service;

use FacebookAds\Api;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\Fields\AdsInsightsFields;
use Psr\Log\LoggerInterface;
use DateTime;


class FacebookAdsService
{
    private ?Api $api = null;
    private bool $isConfigured = false;
    private ?string $lastError = null;

    public function __construct(
        private string $appId,
        private string $appSecret,
        private string $accessToken,
        private string $adAccountId,
        private LoggerInterface $logger
    ) {
        $this->initialize();
    }

    private function initialize(): void
    {
        if (empty($this->appId) || empty($this->appSecret) || empty($this->accessToken)) {
            $this->logger->warning('Facebook Ads API credentials not configured');
            return;
        }

        try {
            Api::init($this->appId, $this->appSecret, $this->accessToken);
            $this->api = Api::instance();
            $this->isConfigured = true;
            $this->logger->info('Facebook Ads API initialized successfully');
        } catch (\Exception $e) {
            $this->lastError = 'Failed to initialize Facebook Ads API: ' . $e->getMessage();
            $this->logger->error($this->lastError);
        }
    }

    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Get list of campaigns from the ad account
     */
    public function getCampaigns(): array
    {
        if (!$this->isConfigured) {
            // Only return mock data if no specific error occurred (just missing config)
            if ($this->lastError === null) {
                return $this->getMockCampaigns();
            }
            return [];
        }

        try {
            $account = new AdAccount($this->adAccountId);
            $campaigns = $account->getCampaigns([
                CampaignFields::ID,
                CampaignFields::NAME,
                CampaignFields::STATUS,
                CampaignFields::OBJECTIVE,
                CampaignFields::CREATED_TIME,
                CampaignFields::UPDATED_TIME,
                CampaignFields::DAILY_BUDGET,
                CampaignFields::LIFETIME_BUDGET,
            ]);

            $result = [];
            foreach ($campaigns as $campaign) {
                $result[] = [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'objective' => $campaign->objective,
                    'created_time' => $campaign->created_time,
                    'updated_time' => $campaign->updated_time,
                    'daily_budget' => $campaign->daily_budget,
                    'lifetime_budget' => $campaign->lifetime_budget,
                ];
            }

            return $result;
        } catch (\Exception $e) {
            $this->lastError = 'Failed to fetch campaigns: ' . $e->getMessage();
            $this->logger->error($this->lastError);
            return [];
        }
    }

    /**
     * Get insights (statistics) for a specific campaign
     */
    public function getCampaignInsights(string $campaignId, string $datePreset = 'last_30d'): array
    {
        if (!$this->isConfigured) {
            if ($this->lastError === null) {
                return $this->getMockCampaignInsights($campaignId);
            }
            return [];
        }

        try {
            $campaign = new Campaign($campaignId);
            $insights = $campaign->getInsights([
                AdsInsightsFields::CAMPAIGN_NAME,
                AdsInsightsFields::IMPRESSIONS,
                AdsInsightsFields::CLICKS,
                AdsInsightsFields::SPEND,
                AdsInsightsFields::REACH,
                AdsInsightsFields::CTR,
                AdsInsightsFields::CPC,
                AdsInsightsFields::CPM,
                AdsInsightsFields::ACTIONS,
            ], [
                'date_preset' => $datePreset,
            ]);

            $result = [];
            foreach ($insights as $insight) {
                $result[] = [
                    'campaign_name' => $insight->campaign_name,
                    'impressions' => $insight->impressions ?? 0,
                    'clicks' => $insight->clicks ?? 0,
                    'spend' => $insight->spend ?? '0.00',
                    'reach' => $insight->reach ?? 0,
                    'ctr' => $insight->ctr ?? '0.00',
                    'cpc' => $insight->cpc ?? '0.00',
                    'cpm' => $insight->cpm ?? '0.00',
                    'actions' => $insight->actions ?? [],
                ];
            }

            return $result;
        } catch (\Exception $e) {
            $this->lastError = 'Failed to fetch campaign insights: ' . $e->getMessage();
            $this->logger->error($this->lastError);
            return [];
        }
    }

    /**
     * Get account overview statistics
     */
    public function getAccountInsights(string $datePreset = 'last_30d'): array
    {
        if (!$this->isConfigured) {
            if ($this->lastError === null) {
                return $this->getMockAccountInsights();
            }

            return [
                'impressions' => 0,
                'clicks' => 0,
                'spend' => '0.00',
                'reach' => 0,
            ];
        }

        try {
            $account = new AdAccount($this->adAccountId);
            $insights = $account->getInsights([
                AdsInsightsFields::IMPRESSIONS,
                AdsInsightsFields::CLICKS,
                AdsInsightsFields::SPEND,
                AdsInsightsFields::REACH,
                AdsInsightsFields::CTR,
            ], [
                'date_preset' => $datePreset,
            ]);

            if (count($insights) > 0) {
                $insight = $insights[0];
                return [
                    'impressions' => $insight->impressions ?? 0,
                    'clicks' => $insight->clicks ?? 0,
                    'spend' => $insight->spend ?? '0.00',
                    'reach' => $insight->reach ?? 0,
                    'ctr' => $insight->ctr ?? '0.00',
                ];
            }

            return [
                'impressions' => 0,
                'clicks' => 0,
                'spend' => '0.00',
                'reach' => 0,
                'ctr' => '0.00',
            ];
        } catch (\Exception $e) {
            $this->lastError = 'Failed to fetch account insights: ' . $e->getMessage();
            $this->logger->error($this->lastError);
            return [
                'impressions' => 0,
                'clicks' => 0,
                'spend' => '0.00',
                'reach' => 0,
                'ctr' => '0.00',
            ];
        }
    }

    private function getMockCampaigns(): array
    {
        return [
            [
                'id' => 'mock_1',
                'name' => 'Summer Sale 2024 - Conversions',
                'status' => 'ACTIVE',
                'objective' => 'OUTCOME_SALES',
                'created_time' => (new DateTime('-10 days'))->format('Y-m-d H:i:s'),
                'updated_time' => (new DateTime('-1 hour'))->format('Y-m-d H:i:s'),
                'daily_budget' => 5000, // $50.00
                'lifetime_budget' => null,
            ],
            [
                'id' => 'mock_2',
                'name' => 'Brand Awareness - Q3',
                'status' => 'ACTIVE',
                'objective' => 'OUTCOME_AWARENESS',
                'created_time' => (new DateTime('-25 days'))->format('Y-m-d H:i:s'),
                'updated_time' => (new DateTime('-2 days'))->format('Y-m-d H:i:s'),
                'daily_budget' => 2000, // $20.00
                'lifetime_budget' => null,
            ],
            [
                'id' => 'mock_3',
                'name' => 'Retargeting - Cart Abandoners',
                'status' => 'PAUSED',
                'objective' => 'OUTCOME_SALES',
                'created_time' => (new DateTime('-45 days'))->format('Y-m-d H:i:s'),
                'updated_time' => (new DateTime('-5 days'))->format('Y-m-d H:i:s'),
                'daily_budget' => null,
                'lifetime_budget' => 50000, // $500.00
            ],
        ];
    }

    private function getMockAccountInsights(): array
    {
        return [
            'impressions' => 125430,
            'clicks' => 4520,
            'spend' => '1240.50',
            'reach' => 85000,
            'ctr' => '3.60',
        ];
    }

    private function getMockCampaignInsights(string $campaignId): array
    {
        // Customizable mock data based on ID implies dynamic responses, keeping it simple for now
        $baseMultiplier = ($campaignId === 'mock_2') ? 2 : 1;
        
        return [
            [
                'campaign_name' => 'Mock Campaign ' . $campaignId,
                'impressions' => 15000 * $baseMultiplier,
                'clicks' => 450 * $baseMultiplier,
                'spend' => number_format(150.25 * $baseMultiplier, 2),
                'reach' => 12000 * $baseMultiplier,
                'ctr' => '3.00',
                'cpc' => '0.33',
                'cpm' => '10.00',
                'actions' => [],
            ]
        ];
    }
}
