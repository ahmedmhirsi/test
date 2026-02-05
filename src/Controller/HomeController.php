<?php

namespace App\Controller;

use App\Repository\OffreEmploiRepository;
use App\Repository\CandidatureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(OffreEmploiRepository $jobRepo, CandidatureRepository $appRepo): Response
    {
        // Fetching data using the new French entities
        return $this->render('home/index.html.twig', [
            'job_count' => $jobRepo->count([]),
            // Mapping statuses: 'Applied' matching 'En attente', etc., based on new Candidature constants/strings
            'applications_applied' => $appRepo->findBy(['statut' => 'En attente'], ['dateDepot' => 'DESC']),
            'applications_interviewing' => $appRepo->findBy(['statut' => 'Entretien'], ['dateDepot' => 'DESC']),
            'applications_hired' => $appRepo->findBy(['statut' => 'Accepté'], ['dateDepot' => 'DESC']),
            'total_applications' => $appRepo->count([]),
        ]);
    }
}
