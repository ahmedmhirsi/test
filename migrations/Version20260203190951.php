<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203190951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE journal_temps (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, duree INT NOT NULL, notes LONGTEXT DEFAULT NULL, user_id INT DEFAULT NULL, tache_id INT NOT NULL, INDEX IDX_836F840AD2235D39 (tache_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE journal_temps ADD CONSTRAINT FK_836F840AD2235D39 FOREIGN KEY (tache_id) REFERENCES tache (id)');
        $this->addSql('ALTER TABLE projet ADD manager_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tache ADD assignee_id INT DEFAULT NULL, ADD ordre INT DEFAULT NULL, ADD date_echeance DATE DEFAULT NULL, ADD jalon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT FK_938720752F6597E4 FOREIGN KEY (jalon_id) REFERENCES jalon (id)');
        $this->addSql('CREATE INDEX IDX_938720752F6597E4 ON tache (jalon_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE journal_temps DROP FOREIGN KEY FK_836F840AD2235D39');
        $this->addSql('DROP TABLE journal_temps');
        $this->addSql('ALTER TABLE projet DROP manager_id');
        $this->addSql('ALTER TABLE tache DROP FOREIGN KEY FK_938720752F6597E4');
        $this->addSql('DROP INDEX IDX_938720752F6597E4 ON tache');
        $this->addSql('ALTER TABLE tache DROP assignee_id, DROP ordre, DROP date_echeance, DROP jalon_id');
    }
}
