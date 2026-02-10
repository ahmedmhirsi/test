<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/projet')]
#[IsGranted('ROLE_USER')]
final class ProjetController extends AbstractController
{
    #[Route(name: 'app_projet_index', methods: ['GET'])]
    public function index(Request $request, ProjetRepository $projetRepository): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'dateDebut');
        $order = strtoupper($request->query->get('order', 'DESC'));

        $allowedSortFields = ['id', 'titre', 'dateDebut', 'dateFin', 'statut', 'priorite', 'budget'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'dateDebut';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $qb = $projetRepository->createQueryBuilder('p');

        if ($search) {
            $qb->andWhere('p.titre LIKE :search OR p.description LIKE :search OR p.statut LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $projets = $qb->orderBy('p.' . $sort, $order)
            ->getQuery()
            ->getResult();

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
            'currentSort' => $sort,
            'currentOrder' => $order,
            'searchTerm' => $search,
        ]);
    }

    #[Route('/export-pdf', name: 'app_projet_export_pdf', methods: ['GET'])]
    public function exportPdf(ProjetRepository $projetRepository): Response
    {
        $projets = $projetRepository->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('projet/pdf.html.twig', [
            'projets' => $projets,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="projets.pdf"',
            ]
        );
    }

    #[Route('/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('projet/new.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_projet_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $projet->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($projet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
    }
}
