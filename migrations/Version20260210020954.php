<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210020954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA9783E3463 FOREIGN KEY (manager_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_50159CA9783E3463 ON projet (manager_id)');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT FK_9387207559EC7D60 FOREIGN KEY (assignee_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_9387207559EC7D60 ON tache (assignee_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA9783E3463');
        $this->addSql('DROP INDEX IDX_50159CA9783E3463 ON projet');
        $this->addSql('ALTER TABLE tache DROP FOREIGN KEY FK_9387207559EC7D60');
        $this->addSql('DROP INDEX IDX_9387207559EC7D60 ON tache');
    }
}
