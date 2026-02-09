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
    public function index(ChannelRepository $channelRepository): Response
    {
        return $this->render('admin/channel/index.html.twig', [
            'channels' => $channelRepository->findAll(),
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
