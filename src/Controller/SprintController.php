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

#[Route('/sprint')]
final class SprintController extends AbstractController
{
    #[Route(name: 'app_sprint_index', methods: ['GET'])]
    public function index(Request $request, SprintRepository $sprintRepository): Response
    {
        $sort = $request->query->get('sort', 'dateDebut');
        $order = strtoupper($request->query->get('order', 'DESC'));

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['id', 'nom', 'dateDebut', 'dateFin', 'statut', 'objectifVelocite'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'dateDebut';
        }

        // Validate order direction
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $sprints = $sprintRepository->createQueryBuilder('s')
            ->orderBy('s.' . $sort, $order)
            ->getQuery()
            ->getResult();

        return $this->render('sprint/index.html.twig', [
            'sprints' => $sprints,
            'currentSort' => $sort,
            'currentOrder' => $order,
        ]);
    }

    #[Route('/new', name: 'app_sprint_new', methods: ['GET', 'POST'])]
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
    public function delete(Request $request, Sprint $sprint, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sprint->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sprint);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sprint_index', [], Response::HTTP_SEE_OTHER);
    }
}
