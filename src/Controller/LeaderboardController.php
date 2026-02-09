<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gamification')]
class LeaderboardController extends AbstractController
{
    #[Route('/leaderboard', name: 'app_gamification_leaderboard')]
    public function index(UserRepository $userRepository): Response
    {
        // Top 10 users by points
        $users = $userRepository->findBy([], ['points' => 'DESC'], 10);

        return $this->render('gamification/leaderboard.html.twig', [
            'users' => $users,
        ]);
    }
}
