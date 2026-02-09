<?php

namespace App\Controller;

use App\Repository\CandidatureRepository;
use App\Repository\FormationRepository;
use App\Repository\OffreEmploiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Client Dashboard Controller
 * 
 * This controller provides a read-only view for client users to:
 * - View available job offers
 * - View available trainings
 * - Check their application statuses
 * 
 * No CRUD operations allowed - clients can only view data.
 */
#[Route('/client')]
class ClientDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_client_dashboard')]
    public function index(
        OffreEmploiRepository $offreRepo,
        FormationRepository $formationRepo,
        CandidatureRepository $candidatureRepo
    ): Response {
        // Get active job offers
        $activeOffers = $offreRepo->findBy(['statut' => 'Active'], ['datePublication' => 'DESC'], 6);

        // Get upcoming trainings
        $trainings = $formationRepo->findAll();
        $upcomingTrainings = array_filter($trainings, fn($f) => $f->isDisponible());
        $upcomingTrainings = array_slice($upcomingTrainings, 0, 4);

        // Get user's applications
        $userApplications = $candidatureRepo->findBy([], ['dateDepot' => 'DESC'], 10);

        // Application statistics
        $stats = [
            'total_offers' => $offreRepo->count(['statut' => 'Active']),
            'total_trainings' => count($upcomingTrainings),
            'pending' => $candidatureRepo->count(['statut' => 'En attente']),
            'interviewing' => $candidatureRepo->count(['statut' => 'Entretien']),
            'accepted' => $candidatureRepo->count(['statut' => 'Accepté']),
            'rejected' => $candidatureRepo->count(['statut' => 'Refusé']),
        ];

        return $this->render('client/dashboard.html.twig', [
            'active_offers' => $activeOffers,
            'upcoming_trainings' => $upcomingTrainings,
            'user_applications' => $userApplications,
            'stats' => $stats,
        ]);
    }

    #[Route('/explorer', name: 'app_client_explore')]
    public function explore(
        OffreEmploiRepository $offreRepo,
        FormationRepository $formationRepo
    ): Response {
        $offers = $offreRepo->findBy(['statut' => 'Active'], ['datePublication' => 'DESC']);
        $formations = $formationRepo->findAll();
        $availableFormations = array_filter($formations, fn($f) => $f->isDisponible());

        return $this->render('client/explore.html.twig', [
            'offers' => $offers,
            'formations' => $availableFormations,
        ]);
    }

    #[Route('/offres', name: 'app_client_offers')]
    public function offers(OffreEmploiRepository $offreRepo): Response
    {
        $offers = $offreRepo->findBy(['statut' => 'Active'], ['datePublication' => 'DESC']);

        return $this->render('client/offers.html.twig', [
            'offers' => $offers,
        ]);
    }

    #[Route('/formations', name: 'app_client_formations')]
    public function formations(FormationRepository $formationRepo): Response
    {
        $formations = $formationRepo->findAll();

        return $this->render('client/formations.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/mes-candidatures', name: 'app_client_applications')]
    public function myApplications(CandidatureRepository $candidatureRepo): Response
    {
        // In a real app, filter by logged-in user
        $applications = $candidatureRepo->findBy([], ['dateDepot' => 'DESC']);

        return $this->render('client/applications.html.twig', [
            'applications' => $applications,
        ]);
    }
}
