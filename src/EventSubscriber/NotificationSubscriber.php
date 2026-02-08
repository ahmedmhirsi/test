<?php

namespace App\EventSubscriber;

use App\Repository\ReclamationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class NotificationSubscriber implements EventSubscriberInterface
{
    private ReclamationRepository $reclamationRepository;
    private Environment $twig;

    public function __construct(ReclamationRepository $reclamationRepository, Environment $twig)
    {
        $this->reclamationRepository = $reclamationRepository;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // Only inject for main requests (not sub-requests)
        if (!$event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();

        // Check if it's an array (controller + method)
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        // Only inject for back-office controllers
        if (is_object($controller) && str_contains(get_class($controller), '\\Controller\\Back\\')) {
            // Count unanswered reclamations
            $unreadCount = $this->reclamationRepository->countEnCours();

            // Get recent unanswered reclamations
            $recentUnread = $this->reclamationRepository->findRecentEnCours(5);

            // Inject into Twig globals
            $this->twig->addGlobal('unreadReclamationsCount', $unreadCount);
            $this->twig->addGlobal('recentUnreadReclamations', $recentUnread);
        }
    }
}
