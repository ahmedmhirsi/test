<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use App\Repository\CandidatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/candidature')]
class CandidatureController extends AbstractController
{
    #[Route('/', name: 'app_candidature_index', methods: ['GET'])]
    public function index(Request $request, CandidatureRepository $candidatureRepository): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'dateDepot');
        $order = strtoupper($request->query->get('order', 'DESC'));

        $allowedSortFields = ['id', 'nomCandidat', 'dateDepot', 'statut'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'dateDepot';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $qb = $candidatureRepository->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.nomCandidat LIKE :search OR c.emailCandidat LIKE :search OR c.statut LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $candidatures = $qb->orderBy('c.' . $sort, $order)
            ->getQuery()
            ->getResult();

        return $this->render('candidature/index.html.twig', [
            'candidatures' => $candidatures,
            'currentSort' => $sort,
            'currentOrder' => $order,
            'searchTerm' => $search,
        ]);
    }

    #[Route('/export-pdf', name: 'app_candidature_export_pdf', methods: ['GET'])]
    public function exportPdf(CandidatureRepository $candidatureRepository): Response
    {
        $candidatures = $candidatureRepository->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('candidature/pdf.html.twig', [
            'candidatures' => $candidatures,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="candidatures.pdf"',
            ]
        );
    }

    #[Route('/new', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, \App\Repository\OffreEmploiRepository $offreEmploiRepository): Response
    {
        $candidature = new Candidature();

        $offreId = $request->query->get('offre');
        if ($offreId) {
            $offre = $offreEmploiRepository->find($offreId);
            if ($offre) {
                $candidature->setOffreEmploi($offre);
            }
        }

        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $cvFile */
            $cvFile = $form->get('cvPath')->getData();

            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cvFile->guessExtension();

                try {
                    $cvFile->move(
                        $this->getParameter('cv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // handle exception
                }

                $candidature->setCvPath($newFilename);
            }

            $entityManager->persist($candidature);
            $entityManager->flush();

            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidature/new.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(Candidature $candidature): Response
    {
        return $this->render('candidature/show.html.twig', [
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $cvFile */
            $cvFile = $form->get('cvPath')->getData();

            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cvFile->guessExtension();

                try {
                    $cvFile->move(
                        $this->getParameter('cv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // handle exception
                }

                $candidature->setCvPath($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidature/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidature->getId(), $request->request->get('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
    }
}
