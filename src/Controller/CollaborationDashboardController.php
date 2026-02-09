<?php

namespace App\Controller;

use App\Repository\ChannelRepository;
use App\Repository\MeetingRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration')]
class CollaborationDashboardController extends AbstractController
{
    #[Route('/', name: 'app_collaboration_dashboard')]
    public function index(
        UserRepository $userRepository,
        MeetingRepository $meetingRepository,
        ChannelRepository $channelRepository,
        MessageRepository $messageRepository
    ): Response {
        // KPIs
        $totalUsers = count($userRepository->findAll());
        $activeUsers = count($userRepository->findActiveUsers());
        $totalMeetings = count($meetingRepository->findAll());
        $upcomingMeetings = $meetingRepository->findUpcomingMeetings();
        $inProgressMeetings = $meetingRepository->findInProgressMeetings();
        $todayMeetings = $meetingRepository->findTodayMeetings();
        
        $totalChannels = count($channelRepository->findAll());
        $activeChannels = count($channelRepository->findActiveChannels());
        $vocalChannels = count($channelRepository->findByType('Vocal'));
        $messageChannels = count($channelRepository->findByType('Message'));
        
        $totalMessages = count($messageRepository->findAll());
        $visibleMessages = count($messageRepository->findVisibleMessages());
        
        // Most active channels
        $mostActiveChannels = $channelRepository->findMostActiveChannels(5);
        
        // Hashtag analytics
        $taskMessages = $messageRepository->findByHashtag('task');
        $decisionMessages = $messageRepository->findByHashtag('decision');
        
        return $this->render('collaboration/dashboard.html.twig', [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_meetings' => $totalMeetings,
            'upcoming_meetings' => $upcomingMeetings,
            'in_progress_meetings' => $inProgressMeetings,
            'today_meetings' => $todayMeetings,
            'total_channels' => $totalChannels,
            'active_channels' => $activeChannels,
            'vocal_channels' => $vocalChannels,
            'message_channels' => $messageChannels,
            'total_messages' => $totalMessages,
            'visible_messages' => $visibleMessages,
            'most_active_channels' => $mostActiveChannels,
            'task_count' => count($taskMessages),
            'decision_count' => count($decisionMessages),
        ]);
    }

    #[Route('/analytics', name: 'app_collaboration_analytics')]
    public function analytics(
        UserRepository $userRepository,
        MeetingRepository $meetingRepository,
        MessageRepository $messageRepository
    ): Response {
        $users = $userRepository->findAll();
        $userStats = [];

        foreach ($users as $user) {
            $messageCount = $messageRepository->countByUser($user->getId());
            $meetingCount = count($user->getMeetingUsers());
            
            $userStats[] = [
                'user' => $user,
                'message_count' => $messageCount,
                'meeting_count' => $meetingCount,
                'participation_score' => $messageCount + ($meetingCount * 5), // Simple scoring
            ];
        }

        // Sort by participation score
        usort($userStats, fn($a, $b) => $b['participation_score'] <=> $a['participation_score']);

        return $this->render('collaboration/analytics.html.twig', [
            'user_stats' => $userStats,
            'meetings' => $meetingRepository->findAll(),
        ]);
    }
}
