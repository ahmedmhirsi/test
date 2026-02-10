<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Repository\SprintRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/kanban')]
#[IsGranted('ROLE_EMPLOYEE')]
final class KanbanController extends AbstractController
{
    #[Route('/', name: 'app_kanban_index', methods: ['GET'])]
    public function index(
        SprintRepository $sprintRepository,
        TacheRepository $tacheRepository,
        Request $request
    ): Response {
        // Get active sprint or the one specified in query
        $sprintId = $request->query->getInt('sprint');

        if ($sprintId) {
            $sprint = $sprintRepository->find($sprintId);
        } else {
            // Find active sprint
            $sprint = $sprintRepository->findOneBy(['statut' => 'actif'])
                ?? $sprintRepository->findOneBy(['statut' => 'active'])
                ?? $sprintRepository->findOneBy(['statut' => 'en_cours']);
        }

        // Get all sprints for the dropdown
        $sprints = $sprintRepository->findBy([], ['dateDebut' => 'DESC']);

        // Get tasks for the selected sprint, grouped by status
        $tasks = [
            'todo' => [],
            'in_progress' => [],
            'review' => [],
            'done' => [],
        ];

        if ($sprint) {
            $allTasks = $tacheRepository->findBy(
                ['sprint' => $sprint],
                ['ordre' => 'ASC', 'id' => 'ASC']
            );

            foreach ($allTasks as $task) {
                $status = strtolower($task->getStatut());

                // Map various status values to our columns
                if (in_array($status, ['todo', 'à faire', 'a_faire', 'backlog'])) {
                    $tasks['todo'][] = $task;
                } elseif (in_array($status, ['in_progress', 'en_cours', 'en cours', 'wip'])) {
                    $tasks['in_progress'][] = $task;
                } elseif (in_array($status, ['review', 'en_revision', 'en révision', 'testing'])) {
                    $tasks['review'][] = $task;
                } elseif (in_array($status, ['done', 'terminé', 'termine', 'completed'])) {
                    $tasks['done'][] = $task;
                } else {
                    // Default to todo for unknown statuses
                    $tasks['todo'][] = $task;
                }
            }
        }

        return $this->render('kanban/index.html.twig', [
            'sprint' => $sprint,
            'sprints' => $sprints,
            'tasks' => $tasks,
        ]);
    }

    #[Route('/update-status', name: 'app_kanban_update_status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        TacheRepository $tacheRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $taskId = $data['taskId'] ?? null;
        $newStatus = $data['status'] ?? null;
        $newOrder = $data['order'] ?? null;

        if (!$taskId || !$newStatus) {
            return new JsonResponse(['error' => 'Missing taskId or status'], 400);
        }

        $task = $tacheRepository->find($taskId);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        // Map frontend status to entity status
        $statusMap = [
            'todo' => 'todo',
            'in_progress' => 'in_progress',
            'review' => 'review',
            'done' => 'done',
        ];

        if (isset($statusMap[$newStatus])) {
            $task->setStatut($statusMap[$newStatus]);
        }

        if ($newOrder !== null) {
            $task->setOrdre((int) $newOrder);
        }

        $entityManager->flush();

        // Check if this task affects a milestone
        $jalon = $task->getJalon();
        $milestoneUpdate = null;

        if ($jalon) {
            $milestoneUpdate = [
                'id' => $jalon->getId(),
                'titre' => $jalon->getTitre(),
                'isValidated' => $jalon->isValidated(),
                'completedTasks' => $jalon->getCompletedTasksCount(),
                'calculatedStatut' => $jalon->getCalculatedStatut(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'taskId' => $task->getId(),
            'newStatus' => $task->getStatut(),
            'milestoneUpdate' => $milestoneUpdate,
        ]);
    }

    #[Route('/quick-log/{id}', name: 'app_kanban_quick_log', methods: ['POST'])]
    public function quickLog(
        Tache $tache,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $duree = $data['duree'] ?? null;

        if (!$duree || $duree < 1) {
            return new JsonResponse(['error' => 'Invalid duration'], 400);
        }

        // Create a new journal entry
        $journalTemps = new \App\Entity\JournalTemps();
        $journalTemps->setDate(new \DateTime());
        $journalTemps->setDuree((int) $duree);
        $journalTemps->setNotes($data['notes'] ?? null);
        $journalTemps->setTache($tache);
        // Set current user
        $journalTemps->setUser($this->getUser());

        $entityManager->persist($journalTemps);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'entry' => [
                'id' => $journalTemps->getId(),
                'duree' => $journalTemps->getDuree(),
                'dureeFormatted' => $journalTemps->getDureeFormatted(),
            ],
            'taskTotalTime' => $tache->getTotalLoggedTime(),
        ]);
    }
}
