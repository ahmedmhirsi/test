<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Form\TacheType;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/tache')]
#[IsGranted('ROLE_USER')]
final class TacheController extends AbstractController
{
    #[Route(name: 'app_tache_index', methods: ['GET'])]
    public function index(Request $request, TacheRepository $tacheRepository): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'id');
        $order = strtoupper($request->query->get('order', 'DESC'));

        $allowedSortFields = ['id', 'titre', 'priorite', 'statut', 'tempsEstime', 'tempsReel'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $qb = $tacheRepository->createQueryBuilder('t');

        if ($search) {
            $qb->andWhere('t.titre LIKE :search OR t.description LIKE :search OR t.statut LIKE :search OR t.priorite LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $taches = $qb->orderBy('t.' . $sort, $order)
            ->getQuery()
            ->getResult();

        return $this->render('tache/index.html.twig', [
            'taches' => $taches,
            'currentSort' => $sort,
            'currentOrder' => $order,
            'searchTerm' => $search,
        ]);
    }

    #[Route('/export-pdf', name: 'app_tache_export_pdf', methods: ['GET'])]
    public function exportPdf(TacheRepository $tacheRepository): Response
    {
        $taches = $tacheRepository->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('tache/pdf.html.twig', [
            'taches' => $taches,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="taches.pdf"',
            ]
        );
    }

    #[Route('/new', name: 'app_tache_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tache = new Tache();
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tache);
            $entityManager->flush();

            return $this->redirectToRoute('app_tache_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tache/new.html.twig', [
            'tache' => $tache,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tache_show', methods: ['GET'])]
    public function show(Tache $tache): Response
    {
        return $this->render('tache/show.html.twig', [
            'tache' => $tache,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tache_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function edit(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tache_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tache/edit.html.twig', [
            'tache' => $tache,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tache_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function delete(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tache->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tache);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tache_index', [], Response::HTTP_SEE_OTHER);
    }
}
