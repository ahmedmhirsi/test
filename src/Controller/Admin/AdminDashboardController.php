<?php

namespace App\Controller\Admin;

use App\Repository\ChannelRepository;
use App\Repository\MeetingRepository;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Service\UserStatusService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        MeetingRepository $meetingRepository,
        ChannelRepository $channelRepository,
        UserStatusService $userStatusService
    ): Response
    {
        // Auto-move inactive users to AFK before showing stats
        $afkCount = $userStatusService->checkAndMoveToAFK(15);

        // Stats
        $stats = [
            'users' => $userRepository->count([]),
            'active_users' => $userRepository->count(['status' => 'Active']),
            'afk_users' => $userRepository->count(['status' => 'AFK']),
            'roles' => $roleRepository->count([]),
            'meetings' => $meetingRepository->count([]),
            'active_meetings' => $meetingRepository->count(['statut' => 'En cours']),
            'channels' => $channelRepository->count([]),
        ];

        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);
        $activeChannels = $channelRepository->findBy(['statut' => 'Actif'], ['id' => 'DESC'], 5);

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'active_channels' => $activeChannels,
            'afk_moved_count' => $afkCount,
        ]);
    }
}
