<?php

namespace App\Controller\Admin;

use App\Repository\ChannelRepository;
use App\Repository\MeetingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard', methods: ['GET'])]
    public function index(
        MeetingRepository $meetingRepository,
        ChannelRepository $channelRepository
    ): Response
    {
        // Stats
        $stats = [
            'meetings' => $meetingRepository->count([]),
            'active_meetings' => $meetingRepository->count(['statut' => 'En cours']),
            'channels' => $channelRepository->count([]),
        ];

        $activeChannels = $channelRepository->findBy(['statut' => 'Actif'], ['id' => 'DESC'], 5);

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'active_channels' => $activeChannels,
        ]);
    }
}
