<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Meeting;
use App\Form\ChannelType;
use App\Repository\ChannelRepository;
use App\Security\Voter\ChannelVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration/channel')]
class ChannelController extends AbstractController
{
    #[Route('/', name: 'app_channel_index', methods: ['GET'])]
    public function index(ChannelRepository $channelRepository): Response
    {
        return $this->render('channel/index.html.twig', [
            'channels' => $channelRepository->findActiveChannels(),
            'vocal_channels' => $channelRepository->findByType('Vocal'),
            'message_channels' => $channelRepository->findByType('Message'),
        ]);
    }

    #[Route('/new', name: 'app_channel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check permission to create channels
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(ChannelVoter::CREATE);
        
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
        // Check permission to view channel
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(ChannelVoter::VIEW, $channel);
        
        return $this->render('channel/show.html.twig', [
            'channel' => $channel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_channel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Channel $channel, EntityManagerInterface $entityManager): Response
    {
        // Check permission to edit channel
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(ChannelVoter::EDIT, $channel);
        
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
        // Check permission to delete channel
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(ChannelVoter::DELETE, $channel);
        
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
        // Check permission to moderate channel
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(ChannelVoter::MODERATE, $channel);
        
        $channel->lockChannel();
        $entityManager->flush();

        $this->addFlash('success', 'Channel verrouillé!');
        return $this->redirectToRoute('app_channel_show', ['id' => $channel->getId()]);
    }
}
