<?php

namespace App\Controller;

use App\Entity\Whiteboard;
use App\Repository\WhiteboardRepository;
// UserRepository removed
// WhiteboardVoter removed
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration/whiteboard')]
class WhiteboardController extends AbstractController
{
    #[Route('/', name: 'app_whiteboard_index', methods: ['GET'])]
    public function index(WhiteboardRepository $whiteboardRepository): Response
    {
        return $this->render('whiteboard/index.html.twig', [
            'whiteboards' => $whiteboardRepository->findAll(),
            'recent' => $whiteboardRepository->findRecentWhiteboards(6),
        ]);
    }

    #[Route('/new', name: 'app_whiteboard_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $whiteboard = new Whiteboard();
            $whiteboard->setTitle($request->request->get('title'));
            $whiteboard->setDescription($request->request->get('description'));
            $whiteboard->setIsPublic($request->request->get('is_public') === '1');
            
            // Created by removed

            // Validate Whiteboard
            $errors = $validator->validate($whiteboard);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->render('whiteboard/new.html.twig');
            }

            $entityManager->persist($whiteboard);
            $entityManager->flush();

            $this->addFlash('success', 'Tableau blanc créé avec succès!');
            return $this->redirectToRoute('app_whiteboard_draw', ['id' => $whiteboard->getId()]);
        }

        return $this->render('whiteboard/new.html.twig');
    }

    #[Route('/{id}', name: 'app_whiteboard_show', methods: ['GET'])]
    public function show(Whiteboard $whiteboard): Response
    {
        return $this->redirectToRoute('app_whiteboard_draw', ['id' => $whiteboard->getId()]);
    }

    #[Route('/{id}/draw', name: 'app_whiteboard_draw', methods: ['GET'])]
    public function draw(Whiteboard $whiteboard): Response
    {
        return $this->render('whiteboard/draw.html.twig', [
            'whiteboard' => $whiteboard,
        ]);
    }

    #[Route('/{id}/save', name: 'app_whiteboard_save', methods: ['POST'])]
    public function save(Whiteboard $whiteboard, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $canvasData = $request->getContent();
        $whiteboard->setCanvasData($canvasData);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Tableau sauvegardé',
            'updated_at' => $whiteboard->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/{id}/load', name: 'app_whiteboard_load', methods: ['GET'])]
    public function load(Whiteboard $whiteboard): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'canvas_data' => $whiteboard->getCanvasData(),
            'updated_at' => $whiteboard->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/{id}/export-pdf', name: 'app_whiteboard_export_pdf', methods: ['POST'])]
    public function exportPdf(Request $request, Whiteboard $whiteboard): Response
    {
        // Get the canvas image data from the request
        $imageData = $request->request->get('image_data');
        
        if (!$imageData) {
            $this->addFlash('error', 'Impossible d\'exporter le tableau vide');
            return $this->redirectToRoute('app_whiteboard_draw', ['id' => $whiteboard->getId()]);
        }

        // Remove the data:image/png;base64, prefix
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = base64_decode($imageData);

        // Create temporary file for the image
        $tempFile = tempnam(sys_get_temp_dir(), 'whiteboard_') . '.png';
        file_put_contents($tempFile, $imageData);

        // Generate PDF
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('whiteboard/pdf.html.twig', [
            'whiteboard' => $whiteboard,
            'image_path' => $tempFile,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Clean up temp file
        @unlink($tempFile);

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="tableau-blanc-' . $whiteboard->getId() . '.pdf"',
            ]
        );
    }

    #[Route('/{id}/delete', name: 'app_whiteboard_delete', methods: ['POST'])]
    public function delete(Request $request, Whiteboard $whiteboard, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$whiteboard->getId(), $request->request->get('_token'))) {
            $entityManager->remove($whiteboard);
            $entityManager->flush();
            $this->addFlash('success', 'Tableau blanc supprimé avec succès!');
        }

        return $this->redirectToRoute('app_whiteboard_index');
    }
}
