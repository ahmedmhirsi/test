<?php

namespace App\Service;

use App\Entity\Meeting;
use Doctrine\ORM\EntityManagerInterface;

class GoogleMeetService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Generate a video conference link for a meeting
     * Using Jitsi Meet (free, no API required) instead of Google Meet
     */
    public function createMeetLink(Meeting $meeting): string
    {
        // Generate a unique room name based on meeting details
        $roomName = $this->generateRoomName($meeting);
        
        // Use Jitsi Meet (free, open-source, no API needed)
        $meetLink = "https://meet.jit.si/" . $roomName;
        
        return $meetLink;
    }

    /**
     * Generate a unique, URL-friendly room name
     */
    private function generateRoomName(Meeting $meeting): string
    {
        // Create a clean, unique room name
        // Format: SmartNexus-MeetingTitle-UniqueID
        $cleanTitle = $this->slugify($meeting->getTitre());
        $uniqueId = substr(md5($meeting->getId() . $meeting->getDateDebut()->format('YmdHis')), 0, 8);
        
        return "SmartNexus-{$cleanTitle}-{$uniqueId}";
    }

    /**
     * Convert string to URL-friendly slug
     */
    private function slugify(string $text): string
    {
        // Replace non letter or digits by hyphens
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim
        $text = trim($text, '-');
        
        // Remove duplicate hyphens
        $text = preg_replace('~-+~', '-', $text);
        
        // Lowercase
        $text = strtolower($text);
        
        return substr($text, 0, 50); // Limit length
    }

    /**
     * Alternative: Create a real Google Meet link using Google Calendar API
     * This requires:
     * 1. Google Cloud Project with Calendar API enabled
     * 2. OAuth2 credentials
     * 3. Service account or user authentication
     * 
     * To implement this:
     * - composer require google/apiclient
     * - Set up credentials in .env
     * - Implement OAuth2 flow
     */
    public function createRealGoogleMeet(Meeting $meeting): ?string
    {
        // TODO: Implement real Google Calendar API integration
        // For now, use Jitsi Meet as it works immediately
        return $this->createMeetLink($meeting);
    }
}
