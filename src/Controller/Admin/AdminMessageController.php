<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/message')]
class AdminMessageController extends AbstractController
{
    #[Route('/', name: 'app_admin_message_index', methods: ['GET'])]
    public function index(Request $request, MessageRepository $messageRepository): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction', 'DESC');

        return $this->render('admin/message/index.html.twig', [
            'messages' => $messageRepository->findBySearchAndSort($search, $sort, $direction),
            'current_search' => $search,
            'current_sort' => $sort,
            'current_direction' => $direction,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $messageContent = substr($message->getContenu(), 0, 50) . '...';
            $messageId = $message->getId();
            
            $entityManager->remove($message);
            $entityManager->flush();

            $auditService->log('DELETE_MESSAGE', 'Message', $messageId, ['content_snippet' => $messageContent]);

            $this->addFlash('success', 'Message supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_message_index');
    }
}
