<?php

namespace App\Controller\Front;

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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twilio\Rest\Client;

#[Route('/front/reclamation')]
class FrontReclamationController extends AbstractController
{
    #[Route('/', name: 'front_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        // Afficher uniquement les réclamations non supprimées par le client
        $reclamations = $reclamationRepository->findAllNotDeletedByClient();

        return $this->render('front/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/new', name: 'front_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $reclamation = new Reclamation();

        // Simplified form for front-office (no status/priority selection)
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'validation_groups' => ['Default']
        ]);

        // Remove status field for front users
        $form->remove('statut');

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

            // Set default status (no need to set priority anymore)
            $reclamation->setStatut('en_cours');

            $entityManager->persist($reclamation);
            $entityManager->flush();

            // Twilio WhatsApp Notification
            $sid = $this->getParameter('twilio_account_sid');
            $token = $this->getParameter('twilio_auth_token');
            $from = $this->getParameter('twilio_whatsapp_from');
            $to = $this->getParameter('twilio_whatsapp_to');

            // Construct message
            $messageBody = "Nouvelle réclamation reçue !\nID: " . $reclamation->getId() . "\nSujet: " . $reclamation->getTitre() . "\nDescription: " . substr($reclamation->getDescription(), 0, 50) . "...\n\nPour répondre, envoyez: REP #" . $reclamation->getId() . ": Votre réponse";

            $messageOptions = [
                "from" => $from,
                "body" => $messageBody
            ];

            // Add media if exists
            if ($reclamation->getPieceJointe()) {
                // Construct absolute URL
                $baseUrl = $_ENV['NGROK_URL'] ?? $request->getSchemeAndHttpHost();
                $mediaUrl = rtrim($baseUrl, '/') . '/uploads/reclamations/' . $reclamation->getPieceJointe();
                $messageOptions['mediaUrl'] = [$mediaUrl];
            }

            try {
                $twilio = new Client($sid, $token);
                $twilio->messages->create(
                    $to,
                    $messageOptions
                );
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Réclamation enregistrée, mais échec de l\'envoi WhatsApp: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Votre réclamation a été envoyée avec succès. Nous vous contacterons bientôt.');

            return $this->redirectToRoute('front_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('front/reclamation/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'front_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Formulaire d'ajout de réponse
        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);
        $reponse->setAuteurType('client');

        // Auto-set author from reclamation email (part before @)
        $emailParts = explode('@', $reclamation->getEmail());
        $auteurNom = $emailParts[0] ?? 'Client';
        $reponse->setAuteur($auteurNom);

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->remove('auteur'); // On gère l'auteur automatiquement, pas via le formulaire

        // Pré-remplir l'auteur si l'utilisateur est connecté (à implémenter ultérieurement)
        // Pour l'instant on laisse le champ libre ou on pourrait le cacher si on avait l'user en session

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            /** @var UploadedFile $pieceJointeFile */
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
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                }
            }

            $reclamation->setStatut('en_cours'); // Réouvrir si le client répond
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('front_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('front/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
            'reponse_form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'front_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Créer le formulaire complet
        $form = $this->createForm(ReclamationType::class, $reclamation);

        // Retirer le champ statut que le client ne peut pas modifier
        $form->remove('statut');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            /** @var UploadedFile $pieceJointeFile */
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
                    // Delete old file if exists? (Optional but good practice)
                    $reclamation->setPieceJointe($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre réclamation a été modifiée avec succès.');

            return $this->redirectToRoute('front_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('front/reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'front_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, ReclamationRepository $reclamationRepository, EntityManagerInterface $entityManager): Response
    {
        $reclamation = $reclamationRepository->find($id);

        if (!$reclamation) {
            // Si la réclamation n'existe pas ou plus, on redirige simplement sans erreur
            return $this->redirectToRoute('front_reclamation_index');
        }

        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            // Soft delete: marquer comme supprimée au lieu de la supprimer réellement
            $reclamation->setDeletedByClient(true);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réclamation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('front_reclamation_index');
    }
}

