<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration/gamification')]
class GamificationController extends AbstractController
{
    #[Route('/', name: 'app_gamification_leaderboard')]
    public function index(MessageRepository $messageRepository): Response
    {
        $topContributors = $messageRepository->getTopContributors(10);

        // Assign badges to each contributor
        foreach ($topContributors as &$contributor) {
            $contributor['badge'] = $this->assignBadge($contributor['message_count']);
        }

        return $this->render('gamification/leaderboard.html.twig', [
            'top_contributors' => $topContributors,
        ]);
    }

    /**
     * Assign badge tier based on message count
     */
    private function assignBadge(int $messageCount): array
    {
        if ($messageCount >= 100) {
            return [
                'name' => 'Légende',
                'icon' => '💎',
                'color' => 'blue',
                'bg_class' => 'bg-blue-100 dark:bg-blue-900/20',
                'text_class' => 'text-blue-600 dark:text-blue-400',
            ];
        } elseif ($messageCount >= 50) {
            return [
                'name' => 'Expert',
                'icon' => '🥇',
                'color' => 'gold',
                'bg_class' => 'bg-gold/10',
                'text_class' => 'text-gold',
            ];
        } elseif ($messageCount >= 10) {
            return [
                'name' => 'Actif',
                'icon' => '🥈',
                'color' => 'gray',
                'bg_class' => 'bg-gray-100 dark:bg-gray-800',
                'text_class' => 'text-gray-600 dark:text-gray-400',
            ];
        } else {
            return [
                'name' => 'Débutant',
                'icon' => '🥉',
                'color' => 'orange',
                'bg_class' => 'bg-orange-100 dark:bg-orange-900/20',
                'text_class' => 'text-orange-600 dark:text-orange-400',
            ];
        }
    }
}
