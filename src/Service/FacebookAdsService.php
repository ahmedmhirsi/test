<?php

namespace App\Service;

use FacebookAds\Api;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\Fields\AdsInsightsFields;
use Psr\Log\LoggerInterface;

class FacebookAdsService
{
    private ?Api $api = null;
    private bool $isConfigured = false;

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
            $this->logger->error('Failed to initialize Facebook Ads API: ' . $e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Get list of campaigns from the ad account
     */
    public function getCampaigns(): array
    {
        if (!$this->isConfigured) {
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
            $this->logger->error('Failed to fetch campaigns: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get insights (statistics) for a specific campaign
     */
    public function getCampaignInsights(string $campaignId, string $datePreset = 'last_30d'): array
    {
        if (!$this->isConfigured) {
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
            $this->logger->error('Failed to fetch campaign insights: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get account overview statistics
     */
    public function getAccountInsights(string $datePreset = 'last_30d'): array
    {
        if (!$this->isConfigured) {
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
            $this->logger->error('Failed to fetch account insights: ' . $e->getMessage());
            return [
                'impressions' => 0,
                'clicks' => 0,
                'spend' => '0.00',
                'reach' => 0,
                'ctr' => '0.00',
            ];
        }
    }
}
