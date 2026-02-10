<?php

namespace App\Controller\Admin;

use App\Entity\Channel;
use App\Form\ChannelType;
use App\Repository\ChannelRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/channel')]
class AdminChannelController extends AbstractController
{
    #[Route('/', name: 'app_admin_channel_index', methods: ['GET'])]
    public function index(Request $request, ChannelRepository $channelRepository): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('admin/channel/index.html.twig', [
            'channels' => $channelRepository->findBySearchAndSort($search, $sort, $direction),
            'current_search' => $search,
            'current_sort' => $sort,
            'current_direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_admin_channel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $channel = new Channel();
        $form = $this->createForm(ChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($channel);
            $entityManager->flush();

            $auditService->log('CREATE_CHANNEL', 'Channel', $channel->getId(), ['name' => $channel->getNom()]);

            $this->addFlash('success', 'Channel created successfully.');

            return $this->redirectToRoute('app_admin_channel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/channel/new.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_channel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Channel $channel, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $form = $this->createForm(ChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $auditService->log('UPDATE_CHANNEL', 'Channel', $channel->getId(), ['name' => $channel->getNom()]);

            $this->addFlash('success', 'Channel updated successfully.');

            return $this->redirectToRoute('app_admin_channel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/channel/edit.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/lock', name: 'app_admin_channel_lock', methods: ['POST'])]
    public function lock(Channel $channel, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $channel->setIsLocked(true);
        $entityManager->flush();

        $auditService->log('LOCK_CHANNEL', 'Channel', $channel->getId(), ['name' => $channel->getNom()]);

        $this->addFlash('success', 'Channel locked successfully.');

        return $this->redirectToRoute('app_admin_channel_index');
    }

    #[Route('/{id}/unlock', name: 'app_admin_channel_unlock', methods: ['POST'])]
    public function unlock(Channel $channel, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $channel->setIsLocked(false);
        $entityManager->flush();

        $auditService->log('UNLOCK_CHANNEL', 'Channel', $channel->getId(), ['name' => $channel->getNom()]);

        $this->addFlash('success', 'Channel unlocked successfully.');

        return $this->redirectToRoute('app_admin_channel_index');
    }

    #[Route('/{id}/delete', name: 'app_admin_channel_delete', methods: ['POST'])]
    public function delete(Request $request, Channel $channel, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$channel->getId(), $request->request->get('_token'))) {
            $channelId = $channel->getId();
            $channelName = $channel->getNom();
            
            $entityManager->remove($channel);
            $entityManager->flush();

            $auditService->log('DELETE_CHANNEL', 'Channel', $channelId, ['name' => $channelName]);
            
            $this->addFlash('success', 'Channel deleted successfully.');
        }

        return $this->redirectToRoute('app_admin_channel_index');
    }
}
