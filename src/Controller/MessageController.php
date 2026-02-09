<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Security\Voter\MessageVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration/message')]
class MessageController extends AbstractController
{
    #[Route('/', name: 'app_message_index', methods: ['GET'])]
    public function index(MessageRepository $messageRepository, Request $request): Response
    {
        $hashtag = $request->query->get('hashtag');
        
        if ($hashtag) {
            $messages = $messageRepository->findByHashtag($hashtag);
        } else {
            $messages = $messageRepository->findVisibleMessages();
        }

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'current_hashtag' => $hashtag,
        ]);
    }

    #[Route('/channel/{channelId}', name: 'app_message_by_channel', methods: ['GET'])]
    public function byChannel(int $channelId, MessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findByChannel($channelId);
        $topic = 'http://127.0.0.1:8000/collaboration/message/channel/' . $channelId;

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'mercureTopic' => $topic,
        ]);
    }

    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        \App\Service\FileUploadService $fileUploader,
        \Symfony\Component\Mercure\HubInterface $hub,
        \App\Service\GamificationService $gamificationService,
        \App\Service\OpenRouterService $openRouterService
    ): Response
    {
        // Check permission to create messages
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MessageVoter::CREATE);
        
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Set current user as author
            $message->setUser($this->getUser());

            // Handle file upload
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $attachmentFile */
            $attachmentFile = $form->get('attachment')->getData();
            if ($attachmentFile) {
                // Get Mime Type BEFORE uploading/moving the file
                $mimeType = $attachmentFile->getMimeType();
                $attachmentFileName = $fileUploader->upload($attachmentFile);
                
                $message->setAttachment($attachmentFileName);
                $message->setAttachmentType($mimeType);
            }

            $entityManager->persist($message);
            $entityManager->flush();

            // Process Gamification
            $gamificationService->processMessage($message);

            // Publish to Mercure
            $topic = 'http://127.0.0.1:8000/collaboration/message/channel/' . $message->getChannel()->getId();
            
            // Serialize message logic (simple array for now, or use Serializer)
            $this->publishMessageToMercure($hub, $topic, $message);

            // --- OpenRouter AI Integration (DeepSeek) ---
            // Check if message starts with @AI, !deepseek, !llama, or !gemini
            $content = $message->getContenu();
            if (str_starts_with(trim($content), '@AI') || str_starts_with(trim($content), '!deepseek') || str_starts_with(trim($content), '!llama')) {
                // Remove the trigger word
                $prompt = trim(str_replace(['@AI', '!deepseek', '!llama', '!gemini'], '', $content));
                
                if (!empty($prompt)) {
                    try {
                        $aiResponse = $openRouterService->chat($prompt);
                        
                        if ($aiResponse) {
                            $aiMessage = new Message();
                            $aiMessage->setContenu($aiResponse);
                            $aiMessage->setUser($this->getUser()); // Sent "by" the user but on behalf of AI
                            $aiMessage->setType('ai');
                            $aiMessage->setChannel($message->getChannel());
                            $aiMessage->setDateEnvoi(new \DateTime());
                            $aiMessage->setStatut('Visible');
                            
                            $entityManager->persist($aiMessage);
                            $entityManager->flush();
                            
                            $this->publishMessageToMercure($hub, $topic, $aiMessage);
                        }
                    } catch (\Exception $e) {
                         // Fail silently or log
                    }
                }
            }

            $this->addFlash('success', 'Message envoyé avec succès!');
            return $this->redirectToRoute('app_message_by_channel', ['channelId' => $message->getChannel()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    private function publishMessageToMercure(\Symfony\Component\Mercure\HubInterface $hub, string $topic, Message $message): void
    {
        $data = json_encode([
            'id' => $message->getId(),
            'content' => $message->getContenu(),
            'user' => $message->getUser()->getNom(),
            'channel' => $message->getChannel()->getId(),
            'attachment' => $message->getAttachment(),
            'type' => $message->getType(),
            'createdAt' => $message->getDateEnvoi()->format('Y-m-d H:i:s'),
        ]);

        $update = new \Symfony\Component\Mercure\Update($topic, $data);

        try {
            $hub->publish($update);
        } catch (\Exception $e) {
            // Log error
        }
    }

    #[Route('/{id}', name: 'app_message_show', methods: ['GET'])]
    public function show(Message $message): Response
    {
        // Check permission to view message
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MessageVoter::VIEW, $message);
        
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        // Check permission to edit message (own message only)
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MessageVoter::EDIT, $message);
        
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Message modifié avec succès!');
            return $this->redirectToRoute('app_message_by_channel', ['channelId' => $message->getChannel()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        // Check permission to delete message
        // TEMPORARILY DISABLED FOR TESTING
        // $this->denyAccessUnlessGranted(MessageVoter::DELETE, $message);
        
        $channelId = $message->getChannel()->getId();

        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $message->deleteMessage();
            $entityManager->flush();
            $this->addFlash('success', 'Message supprimé avec succès!');
        }

        return $this->redirectToRoute('app_message_by_channel', ['channelId' => $channelId], Response::HTTP_SEE_OTHER);
    }

    #[Route('/hashtag/{tag}', name: 'app_message_hashtag', methods: ['GET'])]
    public function byHashtag(string $tag, MessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findByHashtag($tag);

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'current_hashtag' => $tag,
        ]);
    }
}
