<?php

namespace App\Controller;

use App\Repository\OffreEmploiRepository;
use App\Repository\CandidatureRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        OffreEmploiRepository $jobRepo,
        CandidatureRepository $appRepo,
        FormationRepository $formationRepo
    ): Response {
        // If user is logged in, redirect to dashboard (from Main branch logic)
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Fetching data for the landing page (from Recruitment module logic)
        // We'll pass this data to the view, even if the current view doesn't use all of it yet.
        // This allows us to potentially merge the recruitment stats into the home page later if desired.
        
        return $this->render('home/index.html.twig', [
            'job_count' => $jobRepo->count(['statut' => 'Active']),
            'total_applications' => $appRepo->count([]),
            // 'applications_applied' => $appRepo->findBy(['statut' => 'En attente'], ['dateDepot' => 'DESC']),
            // 'applications_interviewing' => $appRepo->findBy(['statut' => 'Entretien'], ['dateDepot' => 'DESC']),
            // 'applications_hired' => $appRepo->findBy(['statut' => 'Accepté'], ['dateDepot' => 'DESC']),

            // Marketplace data
            'offres' => $jobRepo->findBy(['statut' => 'Active'], ['datePublication' => 'DESC'], 6), // Limit to 6 for home
            'formations' => array_slice(array_filter($formationRepo->findAll(), fn($f) => $f->isDisponible()), 0, 3),
        ]);
    }
}
