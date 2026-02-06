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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();

        // Simplified form for front-office (no status/priority selection)
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'validation_groups' => ['Default']
        ]);

        // Remove status and priority fields for front users
        $form->remove('statut');
        $form->remove('priorite');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set default values
            $reclamation->setStatut('ouverte');
            $reclamation->setPriorite('moyenne');

            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réclamation a été envoyée avec succès. Nous vous contacterons bientôt.');

            return $this->redirectToRoute('front_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('front/reclamation/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'front_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // Formulaire pour que le client ajoute une réponse
        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);

        // Définir automatiquement l'auteur à partir de l'email de la réclamation
        $reponse->setAuteur($reclamation->getEmail());

        // Définir le type d'auteur comme client AVANT la validation
        $reponse->setAuteurType('client');

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponse);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réponse a été ajoutée avec succès.');

            return $this->redirectToRoute('front_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('front/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
            'reponse_form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'front_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // Créer le formulaire complet
        $form = $this->createForm(ReclamationType::class, $reclamation);

        // Retirer les champs que le client ne peut pas modifier
        $form->remove('statut');
        $form->remove('priorite');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            // Soft delete: marquer comme supprimée au lieu de la supprimer réellement
            $reclamation->setDeletedByClient(true);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réclamation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('front_reclamation_index');
    }
}

