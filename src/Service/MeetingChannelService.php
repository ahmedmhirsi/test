<?php

namespace App\Service;

use App\Entity\Channel;
use App\Entity\Meeting;
use Doctrine\ORM\EntityManagerInterface;

class MeetingChannelService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuditService $auditService
    ) {
    }

    /**
     * Auto-generate vocal and message channels for a meeting
     */
    public function generateChannelsForMeeting(Meeting $meeting): void
    {
        // Create vocal channel
        $vocalChannel = new Channel();
        $vocalChannel->setNom($meeting->getTitre() . ' - Vocal');
        $vocalChannel->setType('Vocal');
        $vocalChannel->setDescription('Canal vocal pour le meeting: ' . $meeting->getTitre());
        $vocalChannel->setStatut('Actif');
        $vocalChannel->setMaxParticipants(50);

        $this->entityManager->persist($vocalChannel);
        $meeting->setChannelVocal($vocalChannel);

        // Create message channel
        $messageChannel = new Channel();
        $messageChannel->setNom($meeting->getTitre() . ' - Messages');
        $messageChannel->setType('Message');
        $messageChannel->setDescription('Canal de messages pour le meeting: ' . $meeting->getTitre());
        $messageChannel->setStatut('Actif');
        $messageChannel->setMaxParticipants(100);

        $this->entityManager->persist($messageChannel);
        $meeting->setChannelMessage($messageChannel);

        $this->entityManager->flush();

        $this->auditService->log(
            'CREATE_CHANNELS',
            'Meeting',
            $meeting->getId(),
            ['vocal_channel_id' => $vocalChannel->getId(), 'message_channel_id' => $messageChannel->getId()]
        );
    }

    /**
     * Close channels when meeting ends
     */
    public function closeChannelsForMeeting(Meeting $meeting): void
    {
        $vocalChannel = $meeting->getChannelVocal();
        if ($vocalChannel) {
            $vocalChannel->setStatut('Inactif');
        }

        $messageChannel = $meeting->getChannelMessage();
        if ($messageChannel) {
            $messageChannel->setStatut('Inactif');
        }

        $this->entityManager->flush();

        $this->auditService->log(
            'CLOSE_CHANNELS',
            'Meeting',
            $meeting->getId(),
            ['vocal_channel_id' => $vocalChannel?->getId(), 'message_channel_id' => $messageChannel?->getId()]
        );
    }
}
