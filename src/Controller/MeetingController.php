<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Form\MeetingType;
use App\Repository\MeetingRepository;
use App\Service\GoogleMeetService;
use App\Service\MeetingChannelService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/collaboration/meeting')]
class MeetingController extends AbstractController
{
    public function __construct(
        private MeetingChannelService $channelService,
        private NotificationService $notificationService,
        private GoogleMeetService $googleMeetService
    ) {
    }

    #[Route('/', name: 'app_meeting_index', methods: ['GET'])]
    public function index(Request $request, MeetingRepository $meetingRepository): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'date_debut');
        $order = strtoupper($request->query->get('order', 'DESC'));

        $allowedSortFields = ['id', 'titre', 'date_debut', 'duree', 'statut'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'date_debut';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $qb = $meetingRepository->createQueryBuilder('m');

        if ($search) {
            $qb->andWhere('m.titre LIKE :search OR m.description LIKE :search OR m.statut LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $meetings = $qb->orderBy('m.' . $sort, $order)
            ->getQuery()
            ->getResult();

        return $this->render('meeting/index.html.twig', [
            'meetings' => $meetings,
            'upcoming' => $meetingRepository->findUpcomingMeetings(),
            'in_progress' => $meetingRepository->findInProgressMeetings(),
            'currentSort' => $sort,
            'currentOrder' => $order,
            'searchTerm' => $search,
        ]);
    }

    #[Route('/export-pdf', name: 'app_meeting_export_pdf', methods: ['GET'])]
    public function exportPdf(MeetingRepository $meetingRepository): Response
    {
        $meetings = $meetingRepository->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('meeting/pdf.html.twig', [
            'meetings' => $meetings,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="meetings.pdf"',
            ]
        );
    }

    #[Route('/new', name: 'app_meeting_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $meeting = new Meeting();
        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($meeting);
            $entityManager->flush();

            $this->channelService->generateChannelsForMeeting($meeting);

            $this->addFlash('success', 'Meeting créé avec succès!');
            return $this->redirectToRoute('app_meeting_show', ['id' => $meeting->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('meeting/new.html.twig', [
            'meeting' => $meeting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meeting_show', methods: ['GET'])]
    public function show(Meeting $meeting): Response
    {
        return $this->render('meeting/show.html.twig', [
            'meeting' => $meeting,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meeting_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meeting $meeting, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Meeting modifié avec succès!');
            return $this->redirectToRoute('app_meeting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('meeting/edit.html.twig', [
            'meeting' => $meeting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meeting_delete', methods: ['POST'])]
    public function delete(Request $request, Meeting $meeting, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$meeting->getId(), $request->request->get('_token'))) {
            $entityManager->remove($meeting);
            $entityManager->flush();
            $this->addFlash('success', 'Meeting supprimé avec succès!');
        }

        return $this->redirectToRoute('app_meeting_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/start', name: 'app_meeting_start', methods: ['POST'])]
    public function start(Meeting $meeting, EntityManagerInterface $entityManager): Response
    {
        $meeting->startMeeting();

        if (!$meeting->getGoogleMeetLink()) {
            $meetLink = $this->googleMeetService->createMeetLink($meeting);
            $meeting->setGoogleMeetLink($meetLink);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Meeting démarré! Le lien Google Meet a été généré.');

        return $this->render('meeting/start_redirect.html.twig', [
            'meeting' => $meeting,
            'meet_link' => $meeting->getGoogleMeetLink(),
        ]);
    }

    #[Route('/{id}/end', name: 'app_meeting_end', methods: ['POST'])]
    public function end(Meeting $meeting, EntityManagerInterface $entityManager): Response
    {
        $meeting->endMeeting();
        $this->channelService->closeChannelsForMeeting($meeting);
        $entityManager->flush();

        $this->notificationService->notifyMeetingParticipants(
            $meeting,
            "Le meeting '{$meeting->getTitre()}' est terminé."
        );

        $this->addFlash('success', 'Meeting terminé! Les channels ont été fermés.');
        return $this->redirectToRoute('app_meeting_show', ['id' => $meeting->getId()]);
    }
}
