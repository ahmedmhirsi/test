<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Channel;
use App\Entity\Meeting;
use App\Entity\Message;
use App\Entity\MeetingUser;
use App\Entity\UserChannel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CollaborationFixtures extends Fixture
{
    public function __construct(
        private \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Create Users
        $admin = new User();
        $admin->setNom('Samir Mhirsi');
        $admin->setEmail('samir.mhirsi@smartnexus.ai');
        $admin->setRole('Admin');
        $admin->setStatutActif(true);
        $admin->setStatutChannel('Active');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password123'));
        $manager->persist($admin);

        $pm = new User();
        $pm->setNom('Alice Martin');
        $pm->setEmail('alice.martin@smartnexus.ai');
        $pm->setRole('ProjectManager');
        $pm->setStatutActif(true);
        $pm->setStatutChannel('Active');
        $pm->setPassword($this->passwordHasher->hashPassword($pm, 'password123'));
        $manager->persist($pm);

        $member1 = new User();
        $member1->setNom('Bob Dupont');
        $member1->setEmail('bob.dupont@smartnexus.ai');
        $member1->setRole('Member');
        $member1->setStatutActif(true);
        $member1->setStatutChannel('Active');
        $member1->setPassword($this->passwordHasher->hashPassword($member1, 'password123'));
        $manager->persist($member1);

        $member2 = new User();
        $member2->setNom('Claire Dubois');
        $member2->setEmail('claire.dubois@smartnexus.ai');
        $member2->setRole('Member');
        $member2->setStatutActif(true);
        $member2->setStatutChannel('AFK');
        $member2->setPassword($this->passwordHasher->hashPassword($member2, 'password123'));
        $manager->persist($member2);

        // Create Standalone Channels
        $generalChannel = new Channel();
        $generalChannel->setNom('General Discussion');
        $generalChannel->setType('Message');
        $generalChannel->setDescription('Canal général pour toutes les discussions');
        $generalChannel->setStatut('Actif');
        $generalChannel->setMaxParticipants(100);
        $manager->persist($generalChannel);

        $teamVocal = new Channel();
        $teamVocal->setNom('Team Voice Chat');
        $teamVocal->setType('Vocal');
        $teamVocal->setDescription('Canal vocal pour les discussions d\'équipe');
        $teamVocal->setStatut('Actif');
        $teamVocal->setMaxParticipants(50);
        $manager->persist($teamVocal);

        // Create Meeting with Auto-Generated Channels
        $meeting1 = new Meeting();
        $meeting1->setTitre('Sprint Planning - Q1 2026');
        $meeting1->setDateDebut(new \DateTime('today 14:00'));
        $meeting1->setDuree(90);
        $meeting1->setAgenda("1. Review des objectifs Q1\n2. Planification des sprints\n3. Attribution des tâches\n4. Questions & Réponses");
        $meeting1->setStatut('Planifié');
        $manager->persist($meeting1);

        // Auto-generate channels for meeting
        $vocalChannel1 = new Channel();
        $vocalChannel1->setNom($meeting1->getTitre() . ' - Vocal');
        $vocalChannel1->setType('Vocal');
        $vocalChannel1->setDescription('Canal vocal pour le meeting: ' . $meeting1->getTitre());
        $vocalChannel1->setStatut('Actif');
        $vocalChannel1->setMaxParticipants(50);
        $manager->persist($vocalChannel1);
        $meeting1->setChannelVocal($vocalChannel1);

        $messageChannel1 = new Channel();
        $messageChannel1->setNom($meeting1->getTitre() . ' - Messages');
        $messageChannel1->setType('Message');
        $messageChannel1->setDescription('Canal de messages pour le meeting: ' . $meeting1->getTitre());
        $messageChannel1->setStatut('Actif');
        $messageChannel1->setMaxParticipants(100);
        $manager->persist($messageChannel1);
        $meeting1->setChannelMessage($messageChannel1);

        // Second Meeting - In Progress
        $meeting2 = new Meeting();
        $meeting2->setTitre('Daily Standup');
        $meeting2->setDateDebut(new \DateTime('today 10:00'));
        $meeting2->setDuree(15);
        $meeting2->setAgenda("1. Ce qui a été fait hier\n2. Ce qui sera fait aujourd'hui\n3. Blockers");
        $meeting2->setStatut('En cours');
        $manager->persist($meeting2);

        $vocalChannel2 = new Channel();
        $vocalChannel2->setNom($meeting2->getTitre() . ' - Vocal');
        $vocalChannel2->setType('Vocal');
        $vocalChannel2->setDescription('Canal vocal pour le meeting: ' . $meeting2->getTitre());
        $vocalChannel2->setStatut('Actif');
        $vocalChannel2->setMaxParticipants(50);
        $manager->persist($vocalChannel2);
        $meeting2->setChannelVocal($vocalChannel2);

        $messageChannel2 = new Channel();
        $messageChannel2->setNom($meeting2->getTitre() . ' - Messages');
        $messageChannel2->setType('Message');
        $messageChannel2->setDescription('Canal de messages pour le meeting: ' . $meeting2->getTitre());
        $messageChannel2->setStatut('Actif');
        $messageChannel2->setMaxParticipants(100);
        $manager->persist($messageChannel2);
        $meeting2->setChannelMessage($messageChannel2);

        // Add participants to meetings
        $mu1 = new MeetingUser();
        $mu1->setMeeting($meeting1);
        $mu1->setUser($admin);
        $mu1->setRoleInMeeting('ProjectManager');
        $mu1->setAttended(false);
        $manager->persist($mu1);

        $mu2 = new MeetingUser();
        $mu2->setMeeting($meeting1);
        $mu2->setUser($pm);
        $mu2->setRoleInMeeting('ProjectManager');
        $mu2->setAttended(false);
        $manager->persist($mu2);

        $mu3 = new MeetingUser();
        $mu3->setMeeting($meeting1);
        $mu3->setUser($member1);
        $mu3->setRoleInMeeting('Participant');
        $mu3->setAttended(false);
        $manager->persist($mu3);

        $mu4 = new MeetingUser();
        $mu4->setMeeting($meeting2);
        $mu4->setUser($admin);
        $mu4->setRoleInMeeting('Participant');
        $mu4->setAttended(true);
        $manager->persist($mu4);

        // Add users to channels
        $uc1 = new UserChannel();
        $uc1->setUser($admin);
        $uc1->setChannel($generalChannel);
        $uc1->setRoleInChannel('Moderator');
        $manager->persist($uc1);

        $uc2 = new UserChannel();
        $uc2->setUser($pm);
        $uc2->setChannel($generalChannel);
        $uc2->setRoleInChannel('Moderator');
        $manager->persist($uc2);

        $uc3 = new UserChannel();
        $uc3->setUser($member1);
        $uc3->setChannel($generalChannel);
        $uc3->setRoleInChannel('Viewer');
        $manager->persist($uc3);

        // Create Messages with Hashtags
        $message1 = new Message();
        $message1->setContenu("Bonjour à tous ! N'oubliez pas le meeting de planning cet après-midi. #task Préparer vos rapports d'avancement.");
        $message1->setDateEnvoi(new \DateTime('today 09:00'));
        $message1->setStatut('Visible');
        $message1->setVisibility('All');
        $message1->setUser($admin);
        $message1->setChannel($generalChannel);
        $manager->persist($message1);

        $message2 = new Message();
        $message2->setContenu("Suite à notre discussion, #decision nous allons adopter la nouvelle architecture microservices pour le projet.");
        $message2->setDateEnvoi(new \DateTime('today 11:30'));
        $message2->setStatut('Visible');
        $message2->setVisibility('All');
        $message2->setUser($pm);
        $message2->setChannel($generalChannel);
        $manager->persist($message2);

        $message3 = new Message();
        $message3->setContenu("#task Mettre à jour la documentation technique avant vendredi.");
        $message3->setDateEnvoi(new \DateTime('today 12:00'));
        $message3->setStatut('Visible');
        $message3->setVisibility('All');
        $message3->setUser($member1);
        $message3->setChannel($generalChannel);
        $manager->persist($message3);

        $message4 = new Message();
        $message4->setContenu("Le nouveau design est prêt ! Qu'en pensez-vous ? #decision Validation nécessaire avant implémentation.");
        $message4->setDateEnvoi(new \DateTime('today 13:15'));
        $message4->setStatut('Visible');
        $message4->setVisibility('All');
        $message4->setUser($pm);
        $message4->setChannel($generalChannel);
        $manager->persist($message4);

        $message5 = new Message();
        $message5->setContenu("Message dans le channel du meeting - Prêt pour commencer le sprint planning !");
        $message5->setDateEnvoi(new \DateTime('today 13:45'));
        $message5->setStatut('Visible');
        $message5->setVisibility('All');
        $message5->setUser($admin);
        $message5->setChannel($messageChannel1);
        $manager->persist($message5);

        $manager->flush();
    }
}
