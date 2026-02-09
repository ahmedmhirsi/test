<?php

namespace App\Service;

use App\Entity\Meeting;
use App\Entity\Notification;
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
     * Create a notification (Generic, no User entity)
     */
    public function createNotification(string $recipientEmailOrId, string $contenu, string $type, ?Meeting $meeting = null): void
    {
        // Without a User entity, we can't store notifications in the DB linked to a user.
        // We can either drop this feature or send an email directly.
        
        // 2. Send Email if it's a meeting or important alert
        if ($type === 'Meeting' && $this->mailtrapService && filter_var($recipientEmailOrId, FILTER_VALIDATE_EMAIL)) {
            $html = "<h3>Bonjour,</h3>";
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
                $recipientEmailOrId,
                "Notification SmartNexus: " . ($meeting ? $meeting->getTitre() : $type),
                $html
            );
        }
    }

    /**
     * Notify all participants of a meeting
     */
    public function notifyMeetingParticipants(Meeting $meeting, string $message): void
    {
        // Logic to get participants without User entity is tricky if we don't have their emails.
        // If Meeting no longer has MeetingUsers, we can't easily know who to notify unless we store emails on Meeting.
        // For now, we will leave this empty or log a warning.
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsReadForUser(string $userId): void
    {
        // No-op
    }
}
