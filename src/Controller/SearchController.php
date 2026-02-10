<?php

namespace App\Controller;

use App\Repository\ProjetRepository;
use App\Repository\SprintRepository;
use App\Repository\TacheRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(
        Request $request,
        ProjetRepository $projetRepository,
        SprintRepository $sprintRepository,
        TacheRepository $tacheRepository
    ): Response {
        $query = trim($request->query->get('q', ''));

        $results = [
            'projets' => [],
            'sprints' => [],
            'taches' => [],
        ];

        if (strlen($query) >= 2) {
            // Search in Projets by titre
            $results['projets'] = $projetRepository->createQueryBuilder('p')
                ->where('LOWER(p.titre) LIKE LOWER(:query)')
                ->orWhere('LOWER(p.description) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            // Search in Sprints by nom
            $results['sprints'] = $sprintRepository->createQueryBuilder('s')
                ->where('LOWER(s.nom) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            // Search in Taches by titre
            $results['taches'] = $tacheRepository->createQueryBuilder('t')
                ->where('LOWER(t.titre) LIKE LOWER(:query)')
                ->orWhere('LOWER(t.description) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $query . '%')
                ->setMaxResults(15)
                ->getQuery()
                ->getResult();
        }

        $totalResults = count($results['projets']) + count($results['sprints']) + count($results['taches']);

        return $this->render('search/index.html.twig', [
            'query' => $query,
            'results' => $results,
            'totalResults' => $totalResults,
        ]);
    }
}
