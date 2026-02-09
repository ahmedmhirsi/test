<?php

namespace App\Controller\Back;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReclamationType;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/reclamation')]
class BackReclamationController extends AbstractController
{
    #[Route('/', name: 'back_reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        // Récupérer les paramètres de recherche et tri
        $searchId = $request->query->get('id');
        $searchEmail = $request->query->get('email');
        $sort = $request->query->get('sort', 'dateCreation');
        $order = $request->query->get('order', 'DESC');
        $statutFilter = $request->query->get('statut'); // Filtre par statut depuis l'URL

        // Utiliser la méthode searchAndSort pour filtrer et trier
        $reclamations = $reclamationRepository->searchAndSort(
            $searchId ? (int) $searchId : null,
            $searchEmail,
            $sort,
            $order
        );

        // Appliquer le filtre de statut si présent
        if ($statutFilter) {
            $reclamations = array_filter($reclamations, function ($reclamation) use ($statutFilter) {
                return $reclamation->getStatut() === $statutFilter;
            });
        }

        return $this->render('back/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'searchId' => $searchId,
            'searchEmail' => $searchEmail,
            'sort' => $sort,
            'order' => $order,
            'statutFilter' => $statutFilter,
        ]);
    }

    #[Route('/{id}', name: 'back_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager, \Symfony\Component\String\Slugger\SluggerInterface $slugger): Response
    {
        // Form for adding new response
        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);

        // Définir le type d'auteur comme admin AVANT la validation
        $reponse->setAuteurType('admin');

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour automatique du statut si la réclamation est "en_cours"
            if ($reclamation->getStatut() === 'en_cours') {
                $reclamation->setStatut('repondu');
            }

            // Handle file upload
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $pieceJointeFile */
            $pieceJointeFile = $form->get('pieceJointe')->getData();

            if ($pieceJointeFile) {
                $originalFilename = pathinfo($pieceJointeFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pieceJointeFile->guessExtension();

                try {
                    $pieceJointeFile->move(
                        $this->getParameter('reclamations_directory'),
                        $newFilename
                    );
                    $reponse->setPieceJointe($newFilename);
                } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                }
            }

            $entityManager->persist($reponse);
            $entityManager->flush();

            $this->addFlash('success', 'La réponse a été ajoutée avec succès.');

            return $this->redirectToRoute('back_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('back/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
            'reponse_form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'back_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager, \Symfony\Component\String\Slugger\SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $pieceJointeFile */
            $pieceJointeFile = $form->get('pieceJointe')->getData();

            if ($pieceJointeFile) {
                $originalFilename = pathinfo($pieceJointeFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pieceJointeFile->guessExtension();

                try {
                    $pieceJointeFile->move(
                        $this->getParameter('reclamations_directory'),
                        $newFilename
                    );
                    $reclamation->setPieceJointe($newFilename);
                } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'La réclamation a été modifiée avec succès.');

            return $this->redirectToRoute('back_reclamation_index'); // Or show
        }

        return $this->render('back/reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/translate', name: 'back_reclamation_translate', methods: ['POST'])]
    public function translate(
        Request $request,
        Reclamation $reclamation,
        \App\Service\TranslationService $translationService
    ): Response {
        $data = json_decode($request->getContent(), true);
        $targetLanguage = $data['language'] ?? 'en';

        try {
            // Translate title
            $translatedTitle = $translationService->translate(
                $reclamation->getTitre(),
                $targetLanguage
            );

            // Translate description
            $translatedDescription = $translationService->translate(
                $reclamation->getDescription(),
                $targetLanguage
            );

            return $this->json([
                'success' => true,
                'title' => $translatedTitle['translatedText'],
                'description' => $translatedDescription['translatedText'],
                'detectedLanguage' => $translatedTitle['detectedSourceLanguage'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/export-pdf', name: 'back_reclamation_export_pdf', methods: ['GET'])]
    public function exportPdf(Reclamation $reclamation): Response
    {
        // Renvoyer une page HTML optimisée pour l'impression PDF
        return $this->render('back/reclamation/pdf.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/delete', name: 'back_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'La réclamation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('back_reclamation_index');
    }
}
