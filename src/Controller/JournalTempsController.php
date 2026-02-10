<?php

namespace App\Controller;

use App\Entity\JournalTemps;
use App\Form\JournalTempsType;
use App\Repository\JournalTempsRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/journal')]
#[IsGranted('ROLE_EMPLOYEE')]
final class JournalTempsController extends AbstractController
{


    #[Route('/', name: 'app_journal_temps_index', methods: ['GET'])]
    public function index(JournalTempsRepository $journalTempsRepository): Response
    {
        // Filter entries by current user
        $user = $this->getUser();
        $entries = $journalTempsRepository->findBy(
            ['user' => $user],
            ['date' => 'DESC', 'id' => 'DESC']
        );

        // Calculate totals
        $totalMinutes = 0;
        foreach ($entries as $entry) {
            $totalMinutes += $entry->getDuree();
        }

        return $this->render('journal_temps/index.html.twig', [
            'entries' => $entries,
            'totalMinutes' => $totalMinutes,
        ]);
    }

    #[Route('/new', name: 'app_journal_temps_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $journalTemps = new JournalTemps();
        $journalTemps->setDate(new \DateTime());

        $form = $this->createForm(JournalTempsType::class, $journalTemps);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set current user
            $journalTemps->setUser($this->getUser());

            $entityManager->persist($journalTemps);
            $entityManager->flush();

            $this->addFlash('success', 'Entrée de temps enregistrée avec succès.');

            return $this->redirectToRoute('app_journal_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('journal_temps/new.html.twig', [
            'journal_temps' => $journalTemps,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_journal_temps_show', methods: ['GET'])]
    public function show(JournalTemps $journalTemps): Response
    {
        return $this->render('journal_temps/show.html.twig', [
            'journal_temps' => $journalTemps,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_journal_temps_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, JournalTemps $journalTemps, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JournalTempsType::class, $journalTemps);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Entrée de temps modifiée avec succès.');

            return $this->redirectToRoute('app_journal_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('journal_temps/edit.html.twig', [
            'journal_temps' => $journalTemps,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_journal_temps_delete', methods: ['POST'])]
    public function delete(Request $request, JournalTemps $journalTemps, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $journalTemps->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($journalTemps);
            $entityManager->flush();

            $this->addFlash('success', 'Entrée de temps supprimée.');
        }

        return $this->redirectToRoute('app_journal_temps_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/weekly/{year}/{week}', name: 'app_journal_temps_weekly', methods: ['GET'])]
    public function weekly(
        int $year,
        int $week,
        JournalTempsRepository $journalTempsRepository
    ): Response {
        // Calculate week start date
        $weekStart = new \DateTime();
        $weekStart->setISODate($year, $week);
        $weekEnd = (clone $weekStart)->modify('+6 days');

        // Get entries for this week filtered by current user
        $user = $this->getUser();
        $entries = $journalTempsRepository->createQueryBuilder('j')
            ->andWhere('j.user = :user')
            ->andWhere('j.date >= :weekStart')
            ->andWhere('j.date <= :weekEnd')
            ->setParameter('user', $user)
            ->setParameter('weekStart', $weekStart)
            ->setParameter('weekEnd', $weekEnd)
            ->orderBy('j.date', 'ASC')
            ->getQuery()
            ->getResult();

        // Group entries by day
        $entriesByDay = [];
        $currentDate = clone $weekStart;
        for ($i = 0; $i < 7; $i++) {
            $dateKey = $currentDate->format('Y-m-d');
            $entriesByDay[$dateKey] = [
                'date' => clone $currentDate,
                'entries' => [],
                'total' => 0,
            ];
            $currentDate->modify('+1 day');
        }

        foreach ($entries as $entry) {
            $dateKey = $entry->getDate()->format('Y-m-d');
            if (isset($entriesByDay[$dateKey])) {
                $entriesByDay[$dateKey]['entries'][] = $entry;
                $entriesByDay[$dateKey]['total'] += $entry->getDuree();
            }
        }

        // Calculate week total
        $weekTotal = 0;
        foreach ($entriesByDay as $day) {
            $weekTotal += $day['total'];
        }

        return $this->render('journal_temps/weekly.html.twig', [
            'year' => $year,
            'week' => $week,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'entriesByDay' => $entriesByDay,
            'weekTotal' => $weekTotal,
        ]);
    }
}
