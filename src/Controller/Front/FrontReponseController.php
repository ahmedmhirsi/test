<?php

namespace App\Controller\Front;

use App\Entity\Reponse;
use App\Form\ReponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front/reponse')]
class FrontReponseController extends AbstractController
{
    #[Route('/{id}/edit', name: 'front_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        // Security check: Only allow clients to edit their own responses
        if ($reponse->getAuteurType() !== 'client') {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette réponse.');
        }

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre réponse a été modifiée avec succès.');

            return $this->redirectToRoute('front_reclamation_show', ['id' => $reponse->getReclamation()->getId()]);
        }

        return $this->render('front/reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'front_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        // Security check: Only allow clients to delete their own responses
        if ($reponse->getAuteurType() !== 'client') {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette réponse.');
        }

        $reclamationId = $reponse->getReclamation()->getId();

        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réponse a été supprimée avec succès.');
        }

        return $this->redirectToRoute('front_reclamation_show', ['id' => $reclamationId]);
    }
}
