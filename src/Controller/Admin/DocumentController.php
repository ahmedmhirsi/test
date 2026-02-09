<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/documents')]
#[IsGranted('ROLE_ADMIN')]
final class DocumentController extends AbstractController
{
    public function __construct(
        private DocumentRepository $documentRepository,
        private EntityManagerInterface $entityManager,
        private FileUploadService $fileUploadService
    ) {
    }

    #[Route('', name: 'app_admin_document_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search');
        $status = $request->query->get('status');
        $sortField = $request->query->get('sort', 'uploadedAt');
        $sortDirection = $request->query->get('direction', 'DESC');

        $documents = $this->documentRepository->findBySearchAndSort(
            $search,
            $status,
            $sortField,
            $sortDirection
        );

        $statistics = $this->documentRepository->getStatistics();

        return $this->render('admin/document/index.html.twig', [
            'documents' => $documents,
            'statistics' => $statistics,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentSort' => $sortField,
            'currentDirection' => $sortDirection,
        ]);
    }

    #[Route('/upload', name: 'app_admin_document_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request, LoggerInterface $logger): Response
    {
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            // Validation côté serveur pour s'assurer qu'un fichier est fourni
            if (!$file) {
                $this->addFlash('danger', '❌ Veuillez sélectionner un fichier');
                return $this->render('admin/document/upload.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            try {
                // Upload le fichier immédiatement (pas de validation séparée)
                $uploadResult = $this->fileUploadService->uploadDocument($file, $document->getDescription());
                
                // Remplir les informations du document
                $document->setFilename($uploadResult['filename']);
                $document->setOriginalName($uploadResult['originalName']);
                $document->setMimeType($uploadResult['mimeType']);
                $document->setSize($uploadResult['size']);
                $document->setUploadedBy($this->getUser());
                $document->setStatus('pending');

                // Sauvegarder en base
                $this->entityManager->persist($document);
                $this->entityManager->flush();

                // Envoyer à n8n
                $n8nResult = $this->fileUploadService->sendDocumentToN8n(
                    $document->getFilename(),
                    $document->getOriginalName(),
                    $document->getDescription()
                );

                if ($n8nResult['success']) {
                    $document->markAsProcessed(json_encode($n8nResult['response']));
                    $this->addFlash('success', '✅ Document uploadé et envoyé au chatbot avec succès !');
                } else {
                    $document->markAsError($n8nResult['message']);
                    $this->addFlash('warning', '⚠️ Document uploadé mais erreur lors de l\'envoi au chatbot: ' . $n8nResult['message']);
                }

                $this->entityManager->flush();

                return $this->redirectToRoute('app_admin_document_index');

            } catch (\Exception $e) {
                $this->addFlash('danger', '❌ Erreur lors de l\'upload: ' . $e->getMessage());
            }
        }

        return $this->render('admin/document/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('admin/document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_document_delete', methods: ['POST'])]
    public function delete(Request $request, Document $document): Response
    {
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            // Supprimer le fichier physique
            $this->fileUploadService->deleteDocument($document->getFilename());
            
            // Supprimer l'entité
            $this->entityManager->remove($document);
            $this->entityManager->flush();

            $this->addFlash('success', '✅ Document supprimé avec succès');
        }

        return $this->redirectToRoute('app_admin_document_index');
    }

    #[Route('/{id}/retry', name: 'app_admin_document_retry', methods: ['POST'])]
    public function retry(Request $request, Document $document): Response
    {
        if ($this->isCsrfTokenValid('retry' . $document->getId(), $request->request->get('_token'))) {
            // Réessayer l'envoi à n8n
            $n8nResult = $this->fileUploadService->sendDocumentToN8n(
                $document->getFilename(),
                $document->getOriginalName(),
                $document->getDescription()
            );

            if ($n8nResult['success']) {
                $document->markAsProcessed(json_encode($n8nResult['response']));
                $this->addFlash('success', '✅ Document envoyé au chatbot avec succès !');
            } else {
                $document->markAsError($n8nResult['message']);
                $this->addFlash('danger', '❌ Erreur lors de l\'envoi: ' . $n8nResult['message']);
            }

            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_document_show', ['id' => $document->getId()]);
    }
}
