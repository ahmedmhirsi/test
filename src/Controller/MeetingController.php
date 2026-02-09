<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\MeetingUser;
use App\Form\MeetingType;
use App\Repository\MeetingRepository;
use App\Security\Voter\MeetingVoter;
use App\Service\GoogleMeetService;
use App\Service\MeetingChannelService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    public function index(MeetingRepository $meetingRepository): Response
    {
        return $this->render('meeting/index.html.twig', [
            'meetings' => $meetingRepository->findAll(),
            'upcoming' => $meetingRepository->findUpcomingMeetings(),
            'in_progress' => $meetingRepository->findInProgressMeetings(),
        ]);
    }

    #[Route('/new', name: 'app_meeting_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check permission to create meetings
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MeetingVoter::CREATE);
        
        $meeting = new Meeting();
        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $currentUser = $this->getUser();
            if (!$currentUser) {
                $this->addFlash('error', 'Vous devez être connecté pour créer un meeting.');
                return $this->redirectToRoute('app_login');
            }

            $entityManager->persist($meeting);
            
            // Auto-add creator as ProjectManager participant
            $meetingUser = new MeetingUser();
            $meetingUser->setMeeting($meeting);
            $meetingUser->setUser($currentUser);
            $meetingUser->setRoleInMeeting('ProjectManager');
            $entityManager->persist($meetingUser);

            $entityManager->flush();

            // Auto-generate channels for the meeting
            $this->channelService->generateChannelsForMeeting($meeting);

            // Notify participants (including the creator now)
            $this->notificationService->notifyMeetingParticipants(
                $meeting,
                "Vous avez été invité au meeting: {$meeting->getTitre()}"
            );

            $this->addFlash('success', 'Meeting créé avec succès! Vous avez reçu un email de confirmation.');
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
        // Check permission to view meeting
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MeetingVoter::VIEW, $meeting);
        
        return $this->render('meeting/show.html.twig', [
            'meeting' => $meeting,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meeting_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meeting $meeting, EntityManagerInterface $entityManager): Response
    {
        // Check permission to edit meeting
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MeetingVoter::EDIT, $meeting);
        
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
        // Check permission to delete meeting
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MeetingVoter::DELETE, $meeting);
        
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
        // Check permission to start meeting
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MeetingVoter::START, $meeting);
        
        $meeting->startMeeting();
        
        // Generate Google Meet link if not already created
        if (!$meeting->getGoogleMeetLink()) {
            $meetLink = $this->googleMeetService->createMeetLink($meeting);
            $meeting->setGoogleMeetLink($meetLink);
        }
        
        $entityManager->flush();

        $this->notificationService->notifyMeetingParticipants(
            $meeting,
            "Le meeting '{$meeting->getTitre()}' a commencé! Rejoignez sur: {$meeting->getGoogleMeetLink()}"
        );

        $this->addFlash('success', 'Meeting démarré! Le lien Google Meet a été généré.');
        
        // Redirect with JavaScript to open Google Meet in new tab
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
