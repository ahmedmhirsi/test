<?php

namespace App\Service;

use App\Repository\MeetingRepository;
use App\Repository\ChannelRepository;
use App\Repository\PollRepository;
use App\Repository\WhiteboardRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class AnalyticsService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ChannelRepository $channelRepository,
        private MessageRepository $messageRepository,
        private MeetingRepository $meetingRepository
    ) {
    }

    /**
     * Calculate participation rate (MOCKED - Users removed)
     */
    public function getUserParticipationRates(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        return [];
    }

    /**
     * Calculate average meeting duration
     * Average = Σ(duration) / Number of meetings
     */
    public function getAverageMeetingDuration(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        $query = $qb->select('m')
            ->from('App\Entity\Meeting', 'm')
            ->where('m.duree IS NOT NULL');

        if ($startDate) {
            $query->andWhere('m.date_debut >= :startDate')
                  ->setParameter('startDate', $startDate);
        }
        if ($endDate) {
            $query->andWhere('m.date_debut <= :endDate')
                  ->setParameter('endDate', $endDate);
        }

        $meetings = $query->getQuery()->getResult();
        
        if (empty($meetings)) {
            return [
                'average_minutes' => 0,
                'total_meetings' => 0,
                'total_duration_minutes' => 0,
                'average_formatted' => '0m',
                'total_duration_formatted' => '0m'
            ];
        }

        $totalDuration = 0;
        foreach ($meetings as $meeting) {
            if ($meeting->getDuree()) {
                $totalDuration += $meeting->getDuree(); // Already in minutes
            }
        }

        $averageMinutes = $totalDuration / count($meetings);

        return [
            'average_minutes' => round($averageMinutes, 2),
            'average_formatted' => $this->formatDuration($averageMinutes),
            'total_meetings' => count($meetings),
            'total_duration_minutes' => round($totalDuration, 2),
            'total_duration_formatted' => $this->formatDuration($totalDuration)
        ];
    }

    /**
     * Calculate productivity metrics by team (channel)
     */
    public function getTeamProductivity(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $channels = $this->channelRepository->findAll();
        $results = [];

        foreach ($channels as $channel) {
            $qb = $this->entityManager->createQueryBuilder();
            
            // Count total meetings for this channel
            $query = $qb->select('COUNT(m.id)')
                ->from('App\Entity\Meeting', 'm')
                ->where('m.channelVocal = :channel OR m.channelMessage = :channel')
                ->setParameter('channel', $channel);

            if ($startDate) {
                $query->andWhere('m.date_debut >= :startDate')
                      ->setParameter('startDate', $startDate);
            }
            if ($endDate) {
                $query->andWhere('m.date_debut <= :endDate')
                      ->setParameter('endDate', $endDate);
            }

            $totalMeetings = $query->getQuery()->getSingleScalarResult();
            
            // Count completed meetings (status = 'Terminé')
            $completedQb = $this->entityManager->createQueryBuilder();
            $completedQuery = $completedQb->select('COUNT(m.id)')
                ->from('App\Entity\Meeting', 'm')
                ->where('m.channelVocal = :channel OR m.channelMessage = :channel')
                ->andWhere('m.statut = :statut')
                ->setParameter('channel', $channel)
                ->setParameter('statut', 'Terminé');

            if ($startDate) {
                $completedQuery->andWhere('m.date_debut >= :startDate')
                      ->setParameter('startDate', $startDate);
            }
            if ($endDate) {
                $completedQuery->andWhere('m.date_debut <= :endDate')
                      ->setParameter('endDate', $endDate);
            }

            $completedMeetings = $completedQuery->getQuery()->getSingleScalarResult();

            // Count polls created for meetings in this channel
            $pollQb = $this->entityManager->createQueryBuilder();
            $pollQuery = $pollQb->select('COUNT(p.id)')
                ->from('App\Entity\Poll', 'p')
                ->leftJoin('p.meeting', 'm')
                ->where('m.channelVocal = :channel OR m.channelMessage = :channel')
                ->setParameter('channel', $channel);
            
            $pollsCount = $pollQuery->getQuery()->getSingleScalarResult();
            
            // Count whiteboards (global for now, not channel-specific)
            $whiteboardsCount = 0; // count removed

            $completionRate = $totalMeetings > 0 ? ($completedMeetings / $totalMeetings) * 100 : 0;
            
            // Calculate productivity score (composite metric)
            $productivityScore = ($completionRate * 0.5) + 
                                ($pollsCount * 2) + 
                                ($whiteboardsCount * 0.5); // Reduced weight since whiteboards are global

            $results[] = [
                'channel_id' => $channel->getId(),
                'channel_name' => $channel->getNom(),
                'total_meetings' => $totalMeetings,
                'completed_meetings' => $completedMeetings,
                'completion_rate' => round($completionRate, 2),
                'polls_created' => $pollsCount,
                'whiteboards_created' => $whiteboardsCount,
                'productivity_score' => round($productivityScore, 2)
            ];
        }

        // Sort by productivity score
        usort($results, fn($a, $b) => $b['productivity_score'] <=> $a['productivity_score']);

        return $results;
    }

    /**
     * Get overall statistics
     */
    public function getOverallStats(): array
    {
        return [
            'total_users' => 0,
            'total_meetings' => $this->meetingRepository->count([]),
            'total_channels' => $this->channelRepository->count([]),
            // repositories for polls and whiteboards might be missing in constructor
            'total_polls' => 0, 
            'total_whiteboards' => 0,
            'active_meetings' => $this->meetingRepository->count(['statut' => 'En cours']),
        ];
    }

    /**
     * Format duration in minutes to human-readable format
     */
    private function formatDuration(float $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = round($minutes % 60);
        
        if ($hours > 0) {
            return sprintf('%dh %02dm', $hours, $mins);
        }
        return sprintf('%dm', $mins);
    }

    /**
     * Get meeting trends over time
     */
    public function getMeetingTrends(int $days = 30): array
    {
        $startDate = new \DateTime("-{$days} days");
        
        $qb = $this->entityManager->createQueryBuilder();
        $meetings = $qb->select('m')
            ->from('App\Entity\Meeting', 'm')
            ->where('m.date_debut >= :startDate')
            ->setParameter('startDate', $startDate)
            ->orderBy('m.date_debut', 'ASC')
            ->getQuery()
            ->getResult();

        $trends = [];
        foreach ($meetings as $meeting) {
            $date = $meeting->getDateDebut()->format('Y-m-d');
            if (!isset($trends[$date])) {
                $trends[$date] = 0;
            }
            $trends[$date]++;
        }

        return $trends;
    }
}
