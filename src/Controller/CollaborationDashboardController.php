<?php

namespace App\Controller;

use App\Repository\ChannelRepository;
use App\Repository\MeetingRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration')]
class CollaborationDashboardController extends AbstractController
{
    #[Route('/', name: 'app_collaboration_dashboard')]
    public function index(
        MeetingRepository $meetingRepository,
        ChannelRepository $channelRepository,
        MessageRepository $messageRepository
    ): Response {
        // KPIs
        $totalMeetings = count($meetingRepository->findAll());
        $upcomingMeetings = $meetingRepository->findUpcomingMeetings();
        $inProgressMeetings = $meetingRepository->findInProgressMeetings();
        $todayMeetings = $meetingRepository->findTodayMeetings();
        
        $totalChannels = count($channelRepository->findAll());
        $activeChannels = $channelRepository->findActiveChannels(); // Assuming returns array/countable
        $vocalChannels = $channelRepository->findByType('Vocal');
        $messageChannels = $channelRepository->findByType('Message');
        
        $totalMessages = count($messageRepository->findAll());
        $visibleMessages = $messageRepository->findVisibleMessages(); // Assuming returns array/countable
        
        // Most active channels
        $mostActiveChannels = $channelRepository->findMostActiveChannels(5);
        
        // Hashtag analytics
        $taskMessages = $messageRepository->findByHashtag('task');
        $decisionMessages = $messageRepository->findByHashtag('decision');
        
        return $this->render('collaboration/dashboard.html.twig', [
            'total_meetings' => $totalMeetings,
            'upcoming_meetings' => $upcomingMeetings,
            'in_progress_meetings' => $inProgressMeetings,
            'today_meetings' => $todayMeetings,
            'total_channels' => $totalChannels,
            'active_channels' => count($activeChannels),
            'vocal_channels' => count($vocalChannels),
            'message_channels' => count($messageChannels),
            'total_messages' => $totalMessages,
            'visible_messages' => count($visibleMessages),
            'most_active_channels' => $mostActiveChannels,
            'task_count' => count($taskMessages),
            'decision_count' => count($decisionMessages),
        ]);
    }

    #[Route('/analytics', name: 'app_collaboration_analytics')]
    public function analytics(
        MeetingRepository $meetingRepository,
        MessageRepository $messageRepository
    ): Response {
        
        return $this->render('collaboration/analytics.html.twig', [
            'user_stats' => [], // Empty array as users are removed
            'meetings' => $meetingRepository->findAll(),
        ]);
    }
}
