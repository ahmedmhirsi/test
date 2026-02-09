<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\PollOption;
use App\Entity\PollVote;
use App\Repository\PollRepository;
use App\Repository\PollVoteRepository;
// UserRepository removed
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/collaboration/poll')]
class PollController extends AbstractController
{
    #[Route('/', name: 'app_poll_index', methods: ['GET'])]
    public function index(PollRepository $pollRepository): Response
    {
        return $this->render('poll/index.html.twig', [
            'polls' => $pollRepository->findAll(),
            'active_polls' => $pollRepository->findActivePolls(),
        ]);
    }

    #[Route('/new', name: 'app_poll_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $poll = new Poll();
            $poll->setQuestion($request->request->get('question'));
            $poll->setDescription($request->request->get('description'));
            $poll->setAllowMultiple($request->request->get('allow_multiple') === '1');
            $poll->setAnonymous($request->request->get('anonymous') === '1');
            
            // Created by removed

            // Validate Poll
            $errors = $validator->validate($poll);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->render('poll/new.html.twig', [
                    'last_username' => '', // placeholder
                    'error' => null
                ]);
            }

            $entityManager->persist($poll);

            // Add options
            $options = $request->request->all('options');
            foreach ($options as $index => $optionText) {
                if (!empty($optionText)) {
                    $option = new PollOption();
                    $option->setText($optionText);
                    $option->setPosition($index);
                    $option->setPoll($poll);
                    $entityManager->persist($option);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Sondage créé avec succès!');
            return $this->redirectToRoute('app_poll_show', ['id' => $poll->getId()]);
        }

        return $this->render('poll/new.html.twig');
    }

    #[Route('/{id}', name: 'app_poll_show', methods: ['GET'])]
    public function show(Poll $poll, PollVoteRepository $voteRepository, Request $request): Response
    {
        $hasVoted = $voteRepository->hasIpVoted($poll->getId(), $request->getClientIp());

        return $this->render('poll/show.html.twig', [
            'poll' => $poll,
            'has_voted' => $hasVoted,
            'current_user' => null, // Removed
        ]);
    }

    #[Route('/{id}/vote', name: 'app_poll_vote', methods: ['POST'])]
    public function vote(Poll $poll, Request $request, EntityManagerInterface $entityManager, PollVoteRepository $voteRepository): Response
    {
        if ($poll->getStatus() !== 'Active') {
            $this->addFlash('error', 'Ce sondage est fermé.');
            return $this->redirectToRoute('app_poll_show', ['id' => $poll->getId()]);
        }
        
        if ($voteRepository->hasIpVoted($poll->getId(), $request->getClientIp())) {
            $this->addFlash('error', 'Vous avez déjà voté pour ce sondage.');
            return $this->redirectToRoute('app_poll_show', ['id' => $poll->getId()]);
        }

        $optionIds = $request->request->all('options');
        
        if (empty($optionIds)) {
            $this->addFlash('error', 'Veuillez sélectionner au moins une option.');
            return $this->redirectToRoute('app_poll_show', ['id' => $poll->getId()]);
        }

        foreach ($optionIds as $optionId) {
            $option = $entityManager->getRepository(PollOption::class)->find($optionId);
            if ($option && $option->getPoll()->getId() === $poll->getId()) {
                $vote = new PollVote();
                $vote->setOption($option);
                // User assignment removed
                $vote->setIpAddress($request->getClientIp());
                $entityManager->persist($vote);
            }

            if (!$poll->isAllowMultiple()) {
                break; // Only one vote allowed
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Votre vote a été enregistré!');
        return $this->redirectToRoute('app_poll_show', ['id' => $poll->getId()]);
    }

    #[Route('/{id}/close', name: 'app_poll_close', methods: ['POST'])]
    public function close(Poll $poll, EntityManagerInterface $entityManager): Response
    {
        $poll->closePoll();
        $entityManager->flush();

        $this->addFlash('success', 'Sondage fermé avec succès!');
        return $this->redirectToRoute('app_poll_show', ['id' => $poll->getId()]);
    }

    #[Route('/{id}/export-pdf', name: 'app_poll_export_pdf', methods: ['GET'])]
    public function exportPdf(Poll $poll): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('poll/pdf.html.twig', [
            'poll' => $poll,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="sondage-' . $poll->getId() . '.pdf"',
            ]
        );
    }

    #[Route('/{id}/delete', name: 'app_poll_delete', methods: ['POST'])]
    public function delete(Request $request, Poll $poll, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$poll->getId(), $request->request->get('_token'))) {
            $entityManager->remove($poll);
            $entityManager->flush();
            $this->addFlash('success', 'Sondage supprimé avec succès!');
        }

        return $this->redirectToRoute('app_poll_index');
    }
}
