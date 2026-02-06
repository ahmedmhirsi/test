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

        // Utiliser la méthode searchAndSort pour filtrer et trier
        $reclamations = $reclamationRepository->searchAndSort(
            $searchId ? (int) $searchId : null,
            $searchEmail,
            $sort,
            $order
        );

        return $this->render('back/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'searchId' => $searchId,
            'searchEmail' => $searchEmail,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    #[Route('/{id}', name: 'back_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // Form for adding new response
        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);

        // Définir le type d'auteur comme admin AVANT la validation
        $reponse->setAuteurType('admin');

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour automatique du statut si la réclamation est "ouverte"
            if ($reclamation->getStatut() === 'ouverte') {
                $reclamation->setStatut('en_cours');
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
