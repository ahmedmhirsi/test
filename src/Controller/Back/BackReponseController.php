<?php

namespace App\Controller\Back;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/reponse')]
class BackReponseController extends AbstractController
{
    #[Route('/{id}/edit', name: 'back_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La réponse a été modifiée avec succès.');

            return $this->redirectToRoute('back_reclamation_show', ['id' => $reponse->getReclamation()->getId()]);
        }

        return $this->render('back/reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'back_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $reclamationId = $reponse->getReclamation()->getId();

        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();

            $this->addFlash('success', 'La réponse a été supprimée avec succès.');
        }

        return $this->redirectToRoute('back_reclamation_show', ['id' => $reclamationId]);
    }
}
