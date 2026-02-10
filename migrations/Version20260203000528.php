<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203000528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS tache');
        $this->addSql('DROP TABLE IF EXISTS sprint');
        $this->addSql('DROP TABLE IF EXISTS projet');
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');
        
        $this->addSql('CREATE TABLE projet (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, budget DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(30) NOT NULL, priorite VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sprint (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(120) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, objectif_velocite DOUBLE PRECISION DEFAULT NULL, velocite_reelle DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(30) NOT NULL, projet_id INT NOT NULL, INDEX IDX_EF8055B7C18272 (projet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tache (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, statut VARCHAR(20) NOT NULL, priorite VARCHAR(10) NOT NULL, temps_estime INT NOT NULL, temps_reel INT DEFAULT NULL, sprint_id INT NOT NULL, INDEX IDX_938720758C24077B (sprint_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE sprint ADD CONSTRAINT FK_EF8055B7C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT FK_938720758C24077B FOREIGN KEY (sprint_id) REFERENCES sprint (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sprint DROP FOREIGN KEY FK_EF8055B7C18272');
        $this->addSql('ALTER TABLE tache DROP FOREIGN KEY FK_938720758C24077B');
        $this->addSql('DROP TABLE projet');
        $this->addSql('DROP TABLE sprint');
        $this->addSql('DROP TABLE tache');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
