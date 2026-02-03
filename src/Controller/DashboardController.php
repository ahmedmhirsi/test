<?php

namespace App\Controller;

use App\Repository\JalonRepository;
use App\Repository\SprintRepository;
use App\Repository\TacheRepository;
use App\Repository\ProjetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        SprintRepository $sprintRepository,
        TacheRepository $tacheRepository,
        ProjetRepository $projetRepository,
        JalonRepository $jalonRepository
    ): Response {
        // Get all sprints for velocity chart
        $sprints = $sprintRepository->findBy([], ['dateDebut' => 'ASC']);

        // Get upcoming milestones
        $upcomingJalons = $jalonRepository->findUpcoming(5);
        $overdueJalons = $jalonRepository->findOverdue();

        // Try to find the active sprint
        $activeSprint = $sprintRepository->findOneBy(['statut' => 'actif']);
        if (!$activeSprint) {
            $activeSprint = $sprintRepository->findOneBy(['statut' => 'active']);
        }
        if (!$activeSprint) {
            $activeSprint = $sprintRepository->findOneBy(['statut' => 'en_cours']);
        }
        // If still no active sprint, get the most recent one
        if (!$activeSprint && count($sprints) > 0) {
            $activeSprint = $sprints[count($sprints) - 1];
        }

        // Get tasks for active sprint
        $tasks = [];
        $totalPoints = 0;
        $completedPoints = 0;
        if ($activeSprint) {
            $tasks = $tacheRepository->findBy(['sprint' => $activeSprint]);
            foreach ($tasks as $task) {
                $totalPoints += $task->getTempsEstime() ?? 0;
                if (in_array(strtolower($task->getStatut()), ['done', 'terminé', 'termine', 'completed'])) {
                    $completedPoints += $task->getTempsEstime() ?? 0;
                }
            }
        }

        // Calculate statistics
        $pointsRemaining = $totalPoints - $completedPoints;
        $completionPercentage = $totalPoints > 0 ? round(($completedPoints / $totalPoints) * 100) : 0;

        // Calculate velocity data for chart (last 4 sprints)
        $velocityData = [];
        $sprintCount = count($sprints);
        $startIdx = max(0, $sprintCount - 4);
        for ($i = $startIdx; $i < $sprintCount; $i++) {
            $sprint = $sprints[$i];
            $velocityData[] = [
                'name' => 'S' . ($i + 1 - $startIdx + 1),
                'velocity' => $sprint->getVelociteReelle() ?? $sprint->getObjectifVelocite() ?? 0,
                'isActive' => $activeSprint && $sprint->getId() === $activeSprint->getId()
            ];
        }

        // Calculate velocity change percentage
        $velocityChange = null;
        if (count($velocityData) >= 2) {
            $current = $velocityData[count($velocityData) - 1]['velocity'];
            $previous = $velocityData[count($velocityData) - 2]['velocity'];
            if ($previous > 0) {
                $velocityChange = round((($current - $previous) / $previous) * 100);
            }
        }

        // Current velocity
        $currentVelocity = $activeSprint ?
            ($activeSprint->getVelociteReelle() ?? $activeSprint->getObjectifVelocite() ?? 0) : 0;

        // Project count
        $projectCount = count($projetRepository->findAll());

        // AI Prediction (simple calculation based on progress and time remaining)
        $aiPrediction = 50; // Default
        if ($activeSprint && $totalPoints > 0) {
            $startDate = $activeSprint->getDateDebut();
            $endDate = $activeSprint->getDateFin();
            $now = new \DateTime();

            if ($startDate && $endDate && $endDate > $startDate) {
                $totalDays = $startDate->diff($endDate)->days;
                $daysElapsed = $startDate->diff($now)->days;
                $timeProgress = $totalDays > 0 ? min(100, ($daysElapsed / $totalDays) * 100) : 0;

                // Compare task progress to time progress
                if ($completionPercentage >= $timeProgress) {
                    // Ahead of schedule
                    $aiPrediction = min(99, 70 + ($completionPercentage - $timeProgress));
                } else {
                    // Behind schedule
                    $diff = $timeProgress - $completionPercentage;
                    $aiPrediction = max(20, 70 - $diff);
                }
            } else {
                $aiPrediction = 50 + ($completionPercentage / 2);
            }
        }
        $aiPrediction = round($aiPrediction);

        return $this->render('dashboard/index.html.twig', [
            'activeSprint' => $activeSprint,
            'tasks' => $tasks,
            'sprints' => $sprints,
            'velocityData' => $velocityData,
            'velocityChange' => $velocityChange,
            'currentVelocity' => $currentVelocity,
            'totalPoints' => $totalPoints,
            'completedPoints' => $completedPoints,
            'pointsRemaining' => $pointsRemaining,
            'completionPercentage' => $completionPercentage,
            'aiPrediction' => $aiPrediction,
            'projectCount' => $projectCount,
            'upcomingJalons' => $upcomingJalons,
            'overdueJalons' => $overdueJalons,
        ]);
    }
}
