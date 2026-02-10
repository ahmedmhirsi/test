<?php

namespace App\DataFixtures;

use App\Entity\Candidature;
use App\Entity\Channel;
use App\Entity\Formation;
use App\Entity\Jalon;
use App\Entity\JournalTemps;
use App\Entity\MarketingCampaign;
use App\Entity\MarketingChannel;
use App\Entity\MarketingLead;
use App\Entity\Meeting;
use App\Entity\Message;
use App\Entity\OffreEmploi;
use App\Entity\Projet;
use App\Entity\Reclamation;
use App\Entity\Sprint;
use App\Entity\Tache;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Create Users
        $users = [];
        $roles = [
            'admin' => ['ROLE_ADMIN'],
            'pm' => ['ROLE_PROJECT_MANAGER'],
            'employee' => ['ROLE_EMPLOYEE'],
            'client' => ['ROLE_CLIENT'],
            'visiteur' => ['ROLE_VISITEUR']
        ];

        foreach ($roles as $key => $role) {
            $user = new User();
            $user->setEmail($key . '@example.com');
            $user->setRoles($role);
            $user->setPrenom(ucfirst($key));
            $user->setNom('User');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setIsVerified(true);
            $user->setIsActive(true);
            $manager->persist($user);
            $users[$key][] = $user;
        }

        // Create random employees
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail('employee' . $i . '@example.com');
            $user->setRoles(['ROLE_EMPLOYEE']);
            $user->setPrenom('Employee');
            $user->setNom((string)$i);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setIsVerified(true);
            $user->setIsActive(true);
            $manager->persist($user);
            $users['employee'][] = $user;
        }

        // Create random clients
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail('client' . $i . '@example.com');
            $user->setRoles(['ROLE_CLIENT']);
            $user->setPrenom('Client');
            $user->setNom((string)$i);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setIsVerified(true);
            $user->setIsActive(true);
            $manager->persist($user);
            $users['client'][] = $user;
        }

        // 2. Create Projects, Sprints, Taxes, Jalons
        $projects = [];
        for ($i = 1; $i <= 5; $i++) {
            $project = new Projet();
            $project->setTitre('Projet ' . $i);
            $project->setDescription('Description du projet ' . $i);
            $project->setDateDebut(new \DateTime('now'));
            $project->setDateFin((new \DateTime('now'))->modify('+3 month'));
            $project->setStatut($i % 2 == 0 ? 'actif' : 'planifie'); // 'actif' or 'planifie'
            $project->setPriorite('moyenne');
            $project->setBudget(10000 * $i);
            $project->setManager($users['pm'][0]); // Assign to first PM
            $manager->persist($project);
            $projects[] = $project;

            // Sprints
            for ($j = 1; $j <= 3; $j++) {
                $sprint = new Sprint();
                $sprint->setNom('Sprint ' . $j . ' - Projet ' . $i);
                $sprint->setDateDebut(new \DateTime('now'));
                $sprint->setDateFin((new \DateTime('now'))->modify('+2 weeks'));
                $sprint->setStatut('actif'); // 'actif', 'planifie', 'termine'
                $sprint->setObjectifVelocite(20);
                $sprint->setProjet($project);
                $manager->persist($sprint);

                // Jalons linked to Sprint
                $jalon = new Jalon();
                $jalon->setTitre('Jalon Sprint ' . $j);
                $jalon->setDescription('Livrable fin de sprint ' . $j);
                $jalon->setDateEcheance((new \DateTime('now'))->modify('+2 weeks'));
                $jalon->setStatut('en_cours');
                $jalon->setPriorite('haute');
                $jalon->setProjet($project);
                $jalon->setSprint($sprint);
                $manager->persist($jalon);

                // Tasks
                for ($k = 1; $k <= 5; $k++) {
                    $tache = new Tache();
                    $tache->setTitre('Tâche ' . $k . ' - Sprint ' . $j);
                    $tache->setDescription('Description tâche ' . $k);
                    $tache->setStatut('todo');
                    $tache->setPriorite('moyenne');
                    $tache->setTempsEstime(4); // hours
                    $tache->setSprint($sprint);
                    $tache->setAssignee($users['employee'][array_rand($users['employee'])]); // Random employee
                    $tache->setJalon($jalon); // Link to jalon
                    $manager->persist($tache);

                    // Journal Temps
                    if ($k % 2 == 0) {
                        $journal = new JournalTemps();
                        $journal->setTache($tache);
                        $journal->setUtilisateur($tache->getAssignee());
                        $journal->setDate(new \DateTime('now'));
                        $journal->setDuree(2); // hours
                        $journal->setDescription('Travail sur la tâche ' . $k);
                        $manager->persist($journal);
                    }
                }
            }
        }

        // 3. Channels and Meetings
        $channels = [];
        $channelNames = ['Général', 'Annonces', 'Développement', 'Design'];
        foreach ($channelNames as $name) {
            $channel = new Channel();
            $channel->setNom($name);
            $channel->setType('Message');
            $channel->setDescription('Canal de discussion ' . $name);
            $channel->setStatut('Actif');
            $channel->setMaxParticipants(50);
            $manager->persist($channel);
            $channels[] = $channel;

             // Messages
            for ($m = 1; $m <= 5; $m++) {
                $message = new Message();
                $message->setContenu('Message ' . $m . ' dans ' . $name);
                $message->setDateEnvoi(new \DateTime('now'));
                $message->setAuteur($users['employee'][array_rand($users['employee'])]);
                $message->setChannel($channel);
                $manager->persist($message);
            }
        }

        for ($i = 1; $i <= 3; $i++) {
            $meeting = new Meeting();
            $meeting->setTitre('Réunion ' . $i);
            $meeting->setDateDebut((new \DateTime('now'))->modify('+' . $i . ' days'));
            $meeting->setDuree(60);
            $meeting->setStatut('Planifié');
            $meeting->setChannelVocal($channels[0]); // General channel
            $manager->persist($meeting);
        }

        // 4. HR Data
        $offres = [];
        $postes = ['Développeur Symfony', 'Designer UI/UX', 'Chef de Projet'];
        foreach ($postes as $poste) {
            $offre = new OffreEmploi();
            $offre->setPoste($poste);
            $offre->setDescription('Nous recherchons un ' . $poste . ' expérimenté.');
            $offre->setSalaireMin(3000);
            $offre->setSalaireMax(5000);
            $offre->setTypeContrat('CDI');
            $offre->setDatePublication(new \DateTime('now'));
            $offre->setStatut('Active');
            $offre->setCompetencesRequises(['Symfony', 'PHP', 'React']);
            $manager->persist($offre);
            $offres[] = $offre;

            // Candidatures
            for ($c = 1; $c <= 3; $c++) {
                $candidature = new Candidature();
                $candidature->setNomCandidat('Candidat ' . $c);
                $candidature->setEmailCandidat('candidat' . $c . '@test.com');
                $candidature->setDateDepot(new \DateTime('now'));
                $candidature->setStatut('En attente');
                $candidature->setOffreEmploi($offre);
                $candidature->setScoreMatchingIA(rand(50, 95));
                $manager->persist($candidature);
            }
        }

        $formations = ['Symfony 6', 'React Basics', 'Agile Scrum'];
        foreach ($formations as $titre) {
            $formation = new Formation();
            $formation->setTitre($titre);
            $formation->setDescription('Formation complète sur ' . $titre);
            $formation->setDureeHeures(20);
            $formation->setTypeFormation('Technique');
            $formation->setNiveauDifficulte('Intermédiaire');
            $formation->setDateDebut(new \DateTime('now'));
            $formation->setDateFin((new \DateTime('now'))->modify('+1 month'));
            $manager->persist($formation);
        }

        // 5. Marketing Data: Channels, Campaigns, Leads
        $marketingChannels = [];
        $mChannelNames = ['LinkedIn', 'Google Ads', 'Emailing'];
        foreach ($mChannelNames as $name) {
            $mChannel = new MarketingChannel();
            $mChannel->setName($name);
            $mChannel->setType('Paid');
            $mChannel->setDescription('Canal marketing ' . $name);
            $manager->persist($mChannel);
            $marketingChannels[] = $mChannel;
        }

        $campaigns = [];
        $campaignNames = ['Lancement Produit', 'Promotion Été'];
        foreach ($campaignNames as $name) {
            $campaign = new MarketingCampaign();
            $campaign->setName($name);
            $campaign->setObjective('Augmenter les ventes');
            $campaign->setStartDate(new \DateTime('now'));
            $campaign->setStatus('active');
            $campaign->setCreatedBy('Admin');
            $manager->persist($campaign);
            $campaigns[] = $campaign;

            // Leads
            for ($l = 1; $l <= 5; $l++) {
                $lead = new MarketingLead();
                $lead->setCampaign($campaign);
                $lead->setChannel($marketingChannels[array_rand($marketingChannels)]);
                $lead->setCompanyName('Entreprise ' . $l);
                $lead->setContactName('Contact ' . $l);
                $lead->setEmail('lead' . $l . '@entreprise.com');
                $lead->setPosition('Manager');
                $lead->setStatus('new');
                $manager->persist($lead);
            }
        }

        // 6. Reclamations
        for ($i = 1; $i <= 5; $i++) {
            $reclamation = new Reclamation();
            $reclamation->setTitre('Problème ' . $i);
            $reclamation->setDescription('Description du problème ' . $i);
            $reclamation->setEmail('client' . $i . '@example.com');
            $reclamation->setStatut('en_cours');
            $reclamation->setDateCreation(new \DateTime('now'));
            $manager->persist($reclamation);
        }

        $manager->flush();
    }
}
