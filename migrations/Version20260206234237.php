<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206234237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, criteria JSON NOT NULL, points INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE channel (id_channel INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, type VARCHAR(20) NOT NULL, description LONGTEXT DEFAULT NULL, statut VARCHAR(20) NOT NULL, max_participants INT DEFAULT NULL, PRIMARY KEY (id_channel)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE meeting (id_meeting INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, date_debut DATETIME NOT NULL, duree INT NOT NULL, agenda LONGTEXT DEFAULT NULL, statut VARCHAR(20) NOT NULL, google_meet_link VARCHAR(255) DEFAULT NULL, id_channel_vocal INT DEFAULT NULL, id_channel_message INT DEFAULT NULL, INDEX IDX_F515E139A88FDE36 (id_channel_vocal), INDEX IDX_F515E139E8A0CCF6 (id_channel_message), PRIMARY KEY (id_meeting)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE meeting_user (role_in_meeting VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, attended TINYINT NOT NULL, id_meeting INT NOT NULL, id_user INT NOT NULL, INDEX IDX_61622E9B2B884849 (id_meeting), INDEX IDX_61622E9B6B3CA4B (id_user), PRIMARY KEY (id_meeting, id_user)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id_message INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, date_envoi DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, visibility VARCHAR(20) NOT NULL, attachment VARCHAR(255) DEFAULT NULL, attachment_type VARCHAR(50) DEFAULT NULL, id_user INT NOT NULL, id_channel INT NOT NULL, INDEX IDX_B6BD307F6B3CA4B (id_user), INDEX IDX_B6BD307F7C642737 (id_channel), PRIMARY KEY (id_message)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id_notification INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, statut VARCHAR(20) NOT NULL, date_creation DATETIME NOT NULL, id_user INT NOT NULL, INDEX IDX_BF5476CA6B3CA4B (id_user), PRIMARY KEY (id_notification)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE permission (id_permission INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, resource VARCHAR(50) NOT NULL, action VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_E04992AA5E237E06 (name), PRIMARY KEY (id_permission)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE poll (id_poll INT AUTO_INCREMENT NOT NULL, question VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, allow_multiple TINYINT NOT NULL, anonymous TINYINT NOT NULL, id_meeting INT DEFAULT NULL, created_by INT NOT NULL, INDEX IDX_84BCFA452B884849 (id_meeting), INDEX IDX_84BCFA45DE12AB56 (created_by), PRIMARY KEY (id_poll)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE poll_option (id_option INT AUTO_INCREMENT NOT NULL, text VARCHAR(255) NOT NULL, position INT NOT NULL, id_poll INT NOT NULL, INDEX IDX_B68343EBF9CE647 (id_poll), PRIMARY KEY (id_option)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE poll_vote (id_vote INT AUTO_INCREMENT NOT NULL, voted_at DATETIME NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, id_option INT NOT NULL, id_user INT DEFAULT NULL, INDEX IDX_ED568EBE7CB1B55D (id_option), INDEX IDX_ED568EBE6B3CA4B (id_user), PRIMARY KEY (id_vote)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recording (id_recording INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, file_type VARCHAR(50) NOT NULL, file_size BIGINT DEFAULT NULL, duration INT DEFAULT NULL, transcription LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_public TINYINT NOT NULL, id_meeting INT DEFAULT NULL, id_user INT NOT NULL, INDEX IDX_BB532B532B884849 (id_meeting), INDEX IDX_BB532B536B3CA4B (id_user), PRIMARY KEY (id_recording)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role (id_role INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, is_system TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_57698A6A5E237E06 (name), PRIMARY KEY (id_role)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role_permission (id INT AUTO_INCREMENT NOT NULL, assigned_at DATETIME NOT NULL, role_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_6F7DF886D60322AC (role_id), INDEX IDX_6F7DF886FED90CCA (permission_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id_user INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, role VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, statut_actif TINYINT NOT NULL, last_seen_at DATETIME DEFAULT NULL, statut_channel VARCHAR(20) NOT NULL, points INT NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id_user)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_role (id_user INT NOT NULL, id_role INT NOT NULL, INDEX IDX_2DE8C6A36B3CA4B (id_user), INDEX IDX_2DE8C6A3DC499668 (id_role), PRIMARY KEY (id_user, id_role)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_badge (id INT AUTO_INCREMENT NOT NULL, awarded_at DATETIME NOT NULL, id_user INT NOT NULL, badge_id INT NOT NULL, INDEX IDX_1C32B3456B3CA4B (id_user), INDEX IDX_1C32B345F7A2C2FC (badge_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_channel (role_in_channel VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, can_invite TINYINT NOT NULL, can_manage_messages TINYINT NOT NULL, can_create_meetings TINYINT NOT NULL, can_pin_messages TINYINT NOT NULL, id_user INT NOT NULL, id_channel INT NOT NULL, INDEX IDX_FAF4904D6B3CA4B (id_user), INDEX IDX_FAF4904D7C642737 (id_channel), PRIMARY KEY (id_user, id_channel)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_permission (id INT AUTO_INCREMENT NOT NULL, resource_type VARCHAR(50) DEFAULT NULL, resource_id INT DEFAULT NULL, granted TINYINT NOT NULL, granted_at DATETIME NOT NULL, user_id INT NOT NULL, permission_id INT NOT NULL, granted_by INT DEFAULT NULL, INDEX IDX_472E5446A76ED395 (user_id), INDEX IDX_472E5446FED90CCA (permission_id), INDEX IDX_472E5446A5FB753F (granted_by), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE whiteboard (id_whiteboard INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, canvas_data LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, is_public TINYINT NOT NULL, id_meeting INT DEFAULT NULL, created_by INT NOT NULL, INDEX IDX_16AD8302B884849 (id_meeting), INDEX IDX_16AD830DE12AB56 (created_by), PRIMARY KEY (id_whiteboard)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139A88FDE36 FOREIGN KEY (id_channel_vocal) REFERENCES channel (id_channel)');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139E8A0CCF6 FOREIGN KEY (id_channel_message) REFERENCES channel (id_channel)');
        $this->addSql('ALTER TABLE meeting_user ADD CONSTRAINT FK_61622E9B2B884849 FOREIGN KEY (id_meeting) REFERENCES meeting (id_meeting)');
        $this->addSql('ALTER TABLE meeting_user ADD CONSTRAINT FK_61622E9B6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7C642737 FOREIGN KEY (id_channel) REFERENCES channel (id_channel)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA452B884849 FOREIGN KEY (id_meeting) REFERENCES meeting (id_meeting)');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA45DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE poll_option ADD CONSTRAINT FK_B68343EBF9CE647 FOREIGN KEY (id_poll) REFERENCES poll (id_poll)');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE7CB1B55D FOREIGN KEY (id_option) REFERENCES poll_option (id_option)');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE recording ADD CONSTRAINT FK_BB532B532B884849 FOREIGN KEY (id_meeting) REFERENCES meeting (id_meeting)');
        $this->addSql('ALTER TABLE recording ADD CONSTRAINT FK_BB532B536B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886D60322AC FOREIGN KEY (role_id) REFERENCES role (id_role)');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id_permission)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A36B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3DC499668 FOREIGN KEY (id_role) REFERENCES role (id_role)');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B3456B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE user_channel ADD CONSTRAINT FK_FAF4904D6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE user_channel ADD CONSTRAINT FK_FAF4904D7C642737 FOREIGN KEY (id_channel) REFERENCES channel (id_channel)');
        $this->addSql('ALTER TABLE user_permission ADD CONSTRAINT FK_472E5446A76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE user_permission ADD CONSTRAINT FK_472E5446FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id_permission)');
        $this->addSql('ALTER TABLE user_permission ADD CONSTRAINT FK_472E5446A5FB753F FOREIGN KEY (granted_by) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE whiteboard ADD CONSTRAINT FK_16AD8302B884849 FOREIGN KEY (id_meeting) REFERENCES meeting (id_meeting)');
        $this->addSql('ALTER TABLE whiteboard ADD CONSTRAINT FK_16AD830DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E139A88FDE36');
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E139E8A0CCF6');
        $this->addSql('ALTER TABLE meeting_user DROP FOREIGN KEY FK_61622E9B2B884849');
        $this->addSql('ALTER TABLE meeting_user DROP FOREIGN KEY FK_61622E9B6B3CA4B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F6B3CA4B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F7C642737');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA6B3CA4B');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA452B884849');
        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA45DE12AB56');
        $this->addSql('ALTER TABLE poll_option DROP FOREIGN KEY FK_B68343EBF9CE647');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE7CB1B55D');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE6B3CA4B');
        $this->addSql('ALTER TABLE recording DROP FOREIGN KEY FK_BB532B532B884849');
        $this->addSql('ALTER TABLE recording DROP FOREIGN KEY FK_BB532B536B3CA4B');
        $this->addSql('ALTER TABLE role_permission DROP FOREIGN KEY FK_6F7DF886D60322AC');
        $this->addSql('ALTER TABLE role_permission DROP FOREIGN KEY FK_6F7DF886FED90CCA');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A36B3CA4B');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3DC499668');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B3456B3CA4B');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345F7A2C2FC');
        $this->addSql('ALTER TABLE user_channel DROP FOREIGN KEY FK_FAF4904D6B3CA4B');
        $this->addSql('ALTER TABLE user_channel DROP FOREIGN KEY FK_FAF4904D7C642737');
        $this->addSql('ALTER TABLE user_permission DROP FOREIGN KEY FK_472E5446A76ED395');
        $this->addSql('ALTER TABLE user_permission DROP FOREIGN KEY FK_472E5446FED90CCA');
        $this->addSql('ALTER TABLE user_permission DROP FOREIGN KEY FK_472E5446A5FB753F');
        $this->addSql('ALTER TABLE whiteboard DROP FOREIGN KEY FK_16AD8302B884849');
        $this->addSql('ALTER TABLE whiteboard DROP FOREIGN KEY FK_16AD830DE12AB56');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE channel');
        $this->addSql('DROP TABLE meeting');
        $this->addSql('DROP TABLE meeting_user');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE poll');
        $this->addSql('DROP TABLE poll_option');
        $this->addSql('DROP TABLE poll_vote');
        $this->addSql('DROP TABLE recording');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_permission');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE user_badge');
        $this->addSql('DROP TABLE user_channel');
        $this->addSql('DROP TABLE user_permission');
        $this->addSql('DROP TABLE whiteboard');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
