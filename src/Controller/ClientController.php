<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Repository\JalonRepository;
use App\Repository\ProjetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
class ClientController extends AbstractController
{
    #[Route('/dashboard', name: 'app_client_dashboard')]
    public function index(ProjetRepository $projetRepository, JalonRepository $jalonRepository): Response
    {
        // In a real app, we would filter by the client's projects
        // For now, we show all projects as requested by the 4-role demo
        $projets = $projetRepository->findAll();

        // Calculate progress for each project based on completed tasks/sprints
        // This is a simplified calculation for the dashboard
        $projectStats = [];
        foreach ($projets as $projet) {
            $totalSprints = count($projet->getSprints());
            $completedSprints = 0;
            foreach ($projet->getSprints() as $sprint) {
                if (in_array(strtolower($sprint->getStatut()), ['terminé', 'completed', 'done'])) {
                    $completedSprints++;
                }
            }

            $progress = $totalSprints > 0 ? round(($completedSprints / $totalSprints) * 100) : 0;

            $projectStats[] = [
                'projet' => $projet,
                'progress' => $progress,
                'jalons' => $jalonRepository->findBy(['projet' => $projet], ['dateEcheance' => 'ASC'], 3)
            ];
        }

        return $this->render('client/dashboard.html.twig', [
            'projectStats' => $projectStats,
        ]);
    }

    #[Route('/projets', name: 'app_client_projets')]
    public function projets(ProjetRepository $projetRepository): Response
    {
        return $this->render('client/projets.html.twig', [
            'projets' => $projetRepository->findAll(),
        ]);
    }

    #[Route('/jalons', name: 'app_client_jalons')]
    public function jalons(JalonRepository $jalonRepository): Response
    {
        return $this->render('client/jalons.html.twig', [
            'jalons' => $jalonRepository->findBy([], ['dateEcheance' => 'ASC']),
        ]);
    }

    #[Route('/progress', name: 'app_client_progress')]
    public function progress(ProjetRepository $projetRepository): Response
    {
        $projets = $projetRepository->findAll();
        return $this->render('client/progress.html.twig', [
            'projets' => $projets,
        ]);
    }
}
