<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Meeting;
use App\Form\ChannelType;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/collaboration/channel')]
class ChannelController extends AbstractController
{
    #[Route('/', name: 'app_channel_index', methods: ['GET'])]
    public function index(Request $request, ChannelRepository $channelRepository): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'nom');
        $order = strtoupper($request->query->get('order', 'ASC'));

        $allowedSortFields = ['id', 'nom', 'type', 'statut'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'nom';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        $qb = $channelRepository->createQueryBuilder('c');

        if (!$this->isGranted('ROLE_ADMIN')) {
            $qb->andWhere('c.statut = :statut')->setParameter('statut', 'Actif');
        }

        if ($search) {
            $qb->andWhere('c.nom LIKE :search OR c.description LIKE :search OR c.type LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $channels = $qb->orderBy('c.' . $sort, $order)
            ->getQuery()
            ->getResult();

        return $this->render('channel/index.html.twig', [
            'channels' => $channels,
            'vocal_channels' => $channelRepository->findByType('Vocal'),
            'message_channels' => $channelRepository->findByType('Message'),
            'currentSort' => $sort,
            'currentOrder' => $order,
            'searchTerm' => $search,
        ]);
    }

    #[Route('/export-pdf', name: 'app_channel_export_pdf', methods: ['GET'])]
    public function exportPdf(ChannelRepository $channelRepository): Response
    {
        $channels = $channelRepository->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('channel/pdf.html.twig', [
            'channels' => $channels,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="channels.pdf"',
            ]
        );
    }

    #[Route('/new', name: 'app_channel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $channel = new Channel();
        $form = $this->createForm(ChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($channel);

            // Handle Meeting Link
            $meeting = $form->get('meeting')->getData();
            if ($meeting) {
                if ($channel->getType() === 'Vocal') {
                    $meeting->setChannelVocal($channel);
                } elseif ($channel->getType() === 'Message') {
                    $meeting->setChannelMessage($channel);
                }
                $entityManager->persist($meeting);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Channel créé avec succès!');
            return $this->redirectToRoute('app_channel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('channel/new.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_channel_show', methods: ['GET'])]
    public function show(Channel $channel): Response
    {
        return $this->render('channel/show.html.twig', [
            'channel' => $channel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_channel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Channel $channel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Channel modifié avec succès!');
            return $this->redirectToRoute('app_channel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('channel/edit.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_channel_delete', methods: ['POST'])]
    public function delete(Request $request, Channel $channel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$channel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($channel);
            $entityManager->flush();
            $this->addFlash('success', 'Channel supprimé avec succès!');
        }

        return $this->redirectToRoute('app_channel_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/lock', name: 'app_channel_lock', methods: ['POST'])]
    public function lock(Channel $channel, EntityManagerInterface $entityManager): Response
    {
        $channel->lockChannel();
        $entityManager->flush();

        $this->addFlash('success', 'Channel verrouillé!');
        return $this->redirectToRoute('app_channel_show', ['id' => $channel->getId()]);
    }
}
