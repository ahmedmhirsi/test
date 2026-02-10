<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Form\MeetingType;
use App\Repository\MeetingRepository;
use App\Service\CalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration/calendar')]
class CalendarController extends AbstractController
{
    public function __construct(
        private CalendarService $calendarService,
        private MeetingRepository $meetingRepository
    ) {
    }

    #[Route('/', name: 'app_calendar_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('calendar/index.html.twig');
    }

    /**
     * API endpoint to get events for FullCalendar
     */
    #[Route('/events', name: 'app_calendar_events', methods: ['GET'])]
    public function getEvents(Request $request): JsonResponse
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        if (!$start || !$end) {
            return new JsonResponse(['error' => 'Start and end dates are required'], 400);
        }

        try {
            $startDate = new \DateTime($start);
            $endDate = new \DateTime($end);

            $events = $this->calendarService->getEventsForPeriod($startDate, $endDate);

            return new JsonResponse($events);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format'], 400);
        }
    }

    /**
     * Quick create event from calendar
     */
    #[Route('/event/create', name: 'app_calendar_event_create', methods: ['POST'])]
    public function quickCreateEvent(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || !isset($data['start'])) {
            return new JsonResponse(['error' => 'Title and start date are required'], 400);
        }

        try {
            $meeting = new Meeting();
            $meeting->setTitre($data['title']);
            $meeting->setDateDebut(new \DateTime($data['start']));
            $meeting->setDuree($data['duration'] ?? 60);
            $meeting->setAgenda($data['description'] ?? null);
            $meeting->setStatut('Planifié');

            $entityManager->persist($meeting);
            $entityManager->flush();

            $event = $this->calendarService->formatEventForFullCalendar($meeting);

            return new JsonResponse([
                'success' => true,
                'event' => $event,
                'message' => 'Événement créé avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to create event: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Move event (drag & drop)
     */
    #[Route('/event/{id}/move', name: 'app_calendar_event_move', methods: ['POST'])]
    public function moveEvent(
        Meeting $meeting,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['start'])) {
            return new JsonResponse(['error' => 'Start date is required'], 400);
        }

        try {
            $newStart = new \DateTime($data['start']);
            $meeting->setDateDebut($newStart);

            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Événement déplacé avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to move event: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export calendar as iCal file
     */
    #[Route('/export/ical', name: 'app_calendar_export_ical', methods: ['GET'])]
    public function exportIcal(Request $request): Response
    {
        // Get filter parameters
        $statut = $request->query->get('statut');
        
        if ($statut) {
            $meetings = $this->meetingRepository->findByStatut($statut);
        } else {
            $meetings = $this->meetingRepository->findAll();
        }

        $icalContent = $this->calendarService->generateICalFile($meetings);

        $response = new Response($icalContent);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="calendar.ics"');

        return $response;
    }

    /**
     * Get Google Calendar URL for a specific meeting
     */
    #[Route('/export/google/{id}', name: 'app_calendar_export_google', methods: ['GET'])]
    public function exportToGoogleCalendar(Meeting $meeting): Response
    {
        $googleUrl = $this->calendarService->getGoogleCalendarUrl($meeting);
        
        return $this->redirect($googleUrl);
    }

    /**
     * Get calendar statistics
     */
    #[Route('/stats', name: 'app_calendar_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $upcoming = count($this->meetingRepository->findUpcomingMeetings());
        $inProgress = count($this->meetingRepository->findInProgressMeetings());
        $today = count($this->meetingRepository->findTodayMeetings());

        return new JsonResponse([
            'upcoming' => $upcoming,
            'inProgress' => $inProgress,
            'today' => $today,
        ]);
    }
}
