<?php

namespace App\Controller;

use App\Entity\Recording;
use App\Repository\RecordingRepository;
use App\Repository\MeetingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration/recording')]
class RecordingController extends AbstractController
{
    #[Route('/', name: 'app_recording_index', methods: ['GET'])]
    public function index(RecordingRepository $recordingRepository): Response
    {
        return $this->render('recording/index.html.twig', [
            'recordings' => $recordingRepository->findAll(),
            'recent' => $recordingRepository->findRecent(6),
        ]);
    }

    #[Route('/new', name: 'app_recording_new', methods: ['GET'])]
    public function new(MeetingRepository $meetingRepository): Response
    {
        return $this->render('recording/new.html.twig', [
            'meetings' => $meetingRepository->findAll(),
        ]);
    }

    #[Route('/record', name: 'app_recording_record', methods: ['GET'])]
    public function record(Request $request, MeetingRepository $meetingRepository): Response
    {
        $meetingId = $request->query->get('meeting');
        $meeting = $meetingId ? $meetingRepository->find($meetingId) : null;

        return $this->render('recording/record.html.twig', [
            'meeting' => $meeting,
        ]);
    }

    #[Route('/upload', name: 'app_recording_upload', methods: ['POST'])]
    public function upload(
        Request $request,
        EntityManagerInterface $entityManager,
        MeetingRepository $meetingRepository
    ): JsonResponse {
        try {
            $file = $request->files->get('recording');
            $title = $request->request->get('title');
            $duration = $request->request->get('duration');
            $meetingId = $request->request->get('meeting_id');

            if (!$file) {
                return new JsonResponse(['success' => false, 'message' => 'No file uploaded'], 400);
            }

            // Get file info BEFORE moving
            $fileSize = $file->getSize();
            $mimeType = $file->getClientMimeType() ?: 'video/webm';

            // Create uploads directory if it doesn't exist
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/recordings';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.webm';
            
            // Move file
            $file->move($uploadsDir, $filename);

            // Create recording entity
            $recording = new Recording();
            $recording->setTitle($title ?: 'Enregistrement ' . date('d/m/Y H:i'));
            $recording->setFilePath('/uploads/recordings/' . $filename);
            $recording->setFileType($mimeType);
            $recording->setFileSize($fileSize);
            $recording->setDuration((int)$duration);
            $recording->setStatus('completed');

            // User assignment removed

            // Set meeting if provided
            if ($meetingId) {
                $meeting = $meetingRepository->find($meetingId);
                if ($meeting) {
                    $recording->setMeeting($meeting);
                }
            }

            $entityManager->persist($recording);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Enregistrement sauvegardé',
                'recording_id' => $recording->getId(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}', name: 'app_recording_show', methods: ['GET'])]
    public function show(Recording $recording): Response
    {
        return $this->render('recording/show.html.twig', [
            'recording' => $recording,
        ]);
    }

    #[Route('/{id}/download', name: 'app_recording_download', methods: ['GET'])]
    public function download(Recording $recording): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $recording->getFilePath();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found');
        }

        return new BinaryFileResponse($filePath);
    }

    #[Route('/{id}/delete', name: 'app_recording_delete', methods: ['POST'])]
    public function delete(Request $request, Recording $recording, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recording->getId(), $request->request->get('_token'))) {
            // Delete file
            $filePath = $this->getParameter('kernel.project_dir') . '/public' . $recording->getFilePath();
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $entityManager->remove($recording);
            $entityManager->flush();
            $this->addFlash('success', 'Enregistrement supprimé avec succès!');
        }

        return $this->redirectToRoute('app_recording_index');
    }
}
