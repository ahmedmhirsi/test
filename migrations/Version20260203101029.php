<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203101029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE jalon (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, date_echeance DATE NOT NULL, statut VARCHAR(30) NOT NULL, priorite VARCHAR(20) NOT NULL, sprint_id INT DEFAULT NULL, projet_id INT NOT NULL, INDEX IDX_9F9801E48C24077B (sprint_id), INDEX IDX_9F9801E4C18272 (projet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE jalon ADD CONSTRAINT FK_9F9801E48C24077B FOREIGN KEY (sprint_id) REFERENCES sprint (id)');
        $this->addSql('ALTER TABLE jalon ADD CONSTRAINT FK_9F9801E4C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jalon DROP FOREIGN KEY FK_9F9801E48C24077B');
        $this->addSql('ALTER TABLE jalon DROP FOREIGN KEY FK_9F9801E4C18272');
        $this->addSql('DROP TABLE jalon');
    }
}
