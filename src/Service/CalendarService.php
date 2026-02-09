<?php

namespace App\Service;

use App\Entity\Meeting;
use App\Repository\MeetingRepository;

class CalendarService
{
    public function __construct(
        private MeetingRepository $meetingRepository
    ) {
    }

    /**
     * Get events for a specific period formatted for FullCalendar
     */
    public function getEventsForPeriod(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $meetings = $this->meetingRepository->findByDateRange($start, $end);
        
        return array_map(
            fn(Meeting $meeting) => $this->formatEventForFullCalendar($meeting),
            $meetings
        );
    }

    /**
     * Format a Meeting entity for FullCalendar.js
     */
    public function formatEventForFullCalendar(Meeting $meeting): array
    {
        $color = $this->getColorByStatus($meeting->getStatut());
        
        return [
            'id' => $meeting->getId(),
            'title' => $meeting->getTitre(),
            'start' => $meeting->getDateDebut()->format('Y-m-d\TH:i:s'),
            'end' => $meeting->getEndTime()?->format('Y-m-d\TH:i:s'),
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'statut' => $meeting->getStatut(),
                'agenda' => $meeting->getAgenda(),
                'participantCount' => $meeting->getParticipantCount(),
                'googleMeetLink' => $meeting->getGoogleMeetLink(),
                'duration' => $meeting->getDuree(),
            ],
            'url' => '/collaboration/meeting/' . $meeting->getId(),
        ];
    }

    /**
     * Get color based on meeting status
     */
    private function getColorByStatus(?string $statut): string
    {
        return match($statut) {
            'Planifié' => '#3788d8',      // Blue
            'En cours' => '#28a745',      // Green
            'Terminé' => '#6c757d',       // Gray
            default => '#3788d8',
        };
    }

    /**
     * Generate iCal file content for meetings
     */
    public function generateICalFile(array $meetings): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Collaboration App//Calendar//FR\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:Meetings\r\n";
        $ical .= "X-WR-TIMEZONE:Europe/Paris\r\n";

        foreach ($meetings as $meeting) {
            $ical .= $this->formatMeetingAsICalEvent($meeting);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Format a single meeting as iCal event
     */
    private function formatMeetingAsICalEvent(Meeting $meeting): string
    {
        $event = "BEGIN:VEVENT\r\n";
        $event .= "UID:" . $meeting->getId() . "@collaboration-app.local\r\n";
        $event .= "DTSTAMP:" . (new \DateTime())->format('Ymd\THis\Z') . "\r\n";
        $event .= "DTSTART:" . $meeting->getDateDebut()->format('Ymd\THis\Z') . "\r\n";
        
        if ($meeting->getEndTime()) {
            $event .= "DTEND:" . $meeting->getEndTime()->format('Ymd\THis\Z') . "\r\n";
        }
        
        $event .= "SUMMARY:" . $this->escapeICalText($meeting->getTitre()) . "\r\n";
        
        if ($meeting->getAgenda()) {
            $event .= "DESCRIPTION:" . $this->escapeICalText($meeting->getAgenda()) . "\r\n";
        }
        
        if ($meeting->getGoogleMeetLink()) {
            $event .= "URL:" . $meeting->getGoogleMeetLink() . "\r\n";
        }
        
        $event .= "STATUS:" . $this->mapStatusToICalStatus($meeting->getStatut()) . "\r\n";
        $event .= "END:VEVENT\r\n";

        return $event;
    }

    /**
     * Escape text for iCal format
     */
    private function escapeICalText(string $text): string
    {
        $text = str_replace(['\\', ',', ';', "\n"], ['\\\\', '\\,', '\\;', '\\n'], $text);
        return $text;
    }

    /**
     * Map application status to iCal status
     */
    private function mapStatusToICalStatus(?string $statut): string
    {
        return match($statut) {
            'Planifié' => 'CONFIRMED',
            'En cours' => 'CONFIRMED',
            'Terminé' => 'CONFIRMED',
            default => 'TENTATIVE',
        };
    }

    /**
     * Generate Google Calendar URL for a meeting
     */
    public function getGoogleCalendarUrl(Meeting $meeting): string
    {
        $params = [
            'action' => 'TEMPLATE',
            'text' => $meeting->getTitre(),
            'dates' => $meeting->getDateDebut()->format('Ymd\THis\Z') . '/' . 
                       ($meeting->getEndTime()?->format('Ymd\THis\Z') ?? ''),
            'details' => $meeting->getAgenda() ?? '',
        ];

        if ($meeting->getGoogleMeetLink()) {
            $params['location'] = $meeting->getGoogleMeetLink();
        }

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }
}
