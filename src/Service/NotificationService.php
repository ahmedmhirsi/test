<?php

namespace App\Service;

use App\Entity\Meeting;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private ?MailtrapService $mailtrapService = null
    ) {
    }

    /**
     * Create a notification for a user
     */
    public function createNotification(User $user, string $contenu, string $type, ?Meeting $meeting = null): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setContenu($contenu);
        $notification->setType($type);
        $notification->setStatut('NonLu');

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // --- External Notifications ---
        
        // 2. Send Email if it's a meeting or important alert
        if ($type === 'Meeting' && $user->getEmail() && $this->mailtrapService) {
            $html = "<h3>Bonjour " . $user->getNom() . ",</h3>";
            $html .= "<p>$contenu</p>";

            if ($meeting) {
                $meetingUrl = $this->urlGenerator->generate('app_meeting_show', ['id' => $meeting->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                $html .= "<div style='margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px;'>";
                $html .= "<h4>Détails du Meeting</h4>";
                $html .= "<ul>";
                $html .= "<li><strong>Titre :</strong> " . $meeting->getTitre() . "</li>";
                $html .= "<li><strong>Date :</strong> " . ($meeting->getDateDebut() ? $meeting->getDateDebut()->format('d/m/Y H:i') : 'N/A') . "</li>";
                $html .= "</ul>";
                $html .= "<a href='$meetingUrl' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Consulter sur SmartNexus</a>";
                
                if ($meeting->getGoogleMeetLink()) {
                    $html .= "<br><br><a href='" . $meeting->getGoogleMeetLink() . "' style='color: #28a745;'>Rejoindre Google Meet</a>";
                }
                $html .= "</div>";
            }

            $this->mailtrapService->sendEmail(
                $user->getEmail(),
                "Notification SmartNexus: " . ($meeting ? $meeting->getTitre() : $type),
                $html
            );
        }

        return $notification;
    }

    /**
     * Notify all participants of a meeting
     */
    public function notifyMeetingParticipants(Meeting $meeting, string $message): void
    {
        foreach ($meeting->getMeetingUsers() as $meetingUser) {
            $this->createNotification(
                $meetingUser->getUser(),
                $message,
                'Meeting',
                $meeting
            );
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsReadForUser(User $user): void
    {
        $notifications = $this->entityManager
            ->getRepository(Notification::class)
            ->findUnreadByUser($user->getId());

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        $this->entityManager->flush();
    }
}
