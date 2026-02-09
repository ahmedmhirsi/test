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
        // Fetching data using the new French entities
        return $this->render('home/index.html.twig', [
            'job_count' => $jobRepo->count(['statut' => 'Active']),
            'total_applications' => $appRepo->count([]),
            'applications_applied' => $appRepo->findBy(['statut' => 'En attente'], ['dateDepot' => 'DESC']),
            'applications_interviewing' => $appRepo->findBy(['statut' => 'Entretien'], ['dateDepot' => 'DESC']),
            'applications_hired' => $appRepo->findBy(['statut' => 'Accepté'], ['dateDepot' => 'DESC']),

            // Marketplace data
            'offres' => $jobRepo->findBy(['statut' => 'Active'], ['datePublication' => 'DESC']),
            'formations' => array_filter($formationRepo->findAll(), fn($f) => $f->isDisponible()),
        ]);
    }
}
