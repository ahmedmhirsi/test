<?php

namespace App\Controller;

use App\Entity\Sprint;
use App\Form\SprintType;
use App\Repository\SprintRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/sprint')]
#[IsGranted('ROLE_USER')]
final class SprintController extends AbstractController
{
    #[Route(name: 'app_sprint_index', methods: ['GET'])]
    public function index(Request $request, SprintRepository $sprintRepository): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'dateDebut');
        $order = strtoupper($request->query->get('order', 'DESC'));

        $allowedSortFields = ['id', 'nom', 'dateDebut', 'dateFin', 'statut', 'objectifVelocite'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'dateDebut';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $qb = $sprintRepository->createQueryBuilder('s');

        if ($search) {
            $qb->andWhere('s.nom LIKE :search OR s.statut LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $sprints = $qb->orderBy('s.' . $sort, $order)
            ->getQuery()
            ->getResult();

        return $this->render('sprint/index.html.twig', [
            'sprints' => $sprints,
            'currentSort' => $sort,
            'currentOrder' => $order,
            'searchTerm' => $search,
        ]);
    }

    #[Route('/export-pdf', name: 'app_sprint_export_pdf', methods: ['GET'])]
    public function exportPdf(SprintRepository $sprintRepository): Response
    {
        $sprints = $sprintRepository->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('sprint/pdf.html.twig', [
            'sprints' => $sprints,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="sprints.pdf"',
            ]
        );
    }

    #[Route('/new', name: 'app_sprint_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sprint = new Sprint();
        $form = $this->createForm(SprintType::class, $sprint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sprint);
            $entityManager->flush();

            return $this->redirectToRoute('app_sprint_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sprint/new.html.twig', [
            'sprint' => $sprint,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sprint_show', methods: ['GET'])]
    public function show(Sprint $sprint): Response
    {
        return $this->render('sprint/show.html.twig', [
            'sprint' => $sprint,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sprint_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function edit(Request $request, Sprint $sprint, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SprintType::class, $sprint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sprint_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sprint/edit.html.twig', [
            'sprint' => $sprint,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sprint_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function delete(Request $request, Sprint $sprint, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sprint->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sprint);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sprint_index', [], Response::HTTP_SEE_OTHER);
    }
}
