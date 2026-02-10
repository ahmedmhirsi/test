<?php

namespace App\Controller;

use App\Entity\Jalon;
use App\Form\JalonType;
use App\Repository\JalonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/jalon')]
#[IsGranted('ROLE_USER')]
final class JalonController extends AbstractController
{
    #[Route(name: 'app_jalon_index', methods: ['GET'])]
    public function index(JalonRepository $jalonRepository): Response
    {
        return $this->render('jalon/index.html.twig', [
            'jalons' => $jalonRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_jalon_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jalon = new Jalon();
        $form = $this->createForm(JalonType::class, $jalon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($jalon);
            $entityManager->flush();

            $this->addFlash('success', 'Jalon créé avec succès !');

            return $this->redirectToRoute('app_jalon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jalon/new.html.twig', [
            'jalon' => $jalon,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_jalon_show', methods: ['GET'])]
    public function show(Jalon $jalon): Response
    {
        return $this->render('jalon/show.html.twig', [
            'jalon' => $jalon,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_jalon_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function edit(Request $request, Jalon $jalon, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JalonType::class, $jalon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Jalon modifié avec succès !');

            return $this->redirectToRoute('app_jalon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jalon/edit.html.twig', [
            'jalon' => $jalon,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_jalon_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function delete(Request $request, Jalon $jalon, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $jalon->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($jalon);
            $entityManager->flush();

            $this->addFlash('success', 'Jalon supprimé avec succès !');
        }

        return $this->redirectToRoute('app_jalon_index', [], Response::HTTP_SEE_OTHER);
    }
}
