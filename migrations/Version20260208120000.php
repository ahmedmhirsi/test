<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajout des colonnes OAuth Google (google_id, oauth_provider)
 */
final class Version20260208120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des colonnes google_id et oauth_provider pour l\'authentification OAuth';
    }

    public function up(Schema $schema): void
    {
        // Ajout des colonnes OAuth
        $this->addSql('ALTER TABLE utilisateur ADD google_id VARCHAR(255) DEFAULT NULL, ADD oauth_provider VARCHAR(50) DEFAULT NULL');
        
        // Ajout de l'index unique sur google_id
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B376F5C865 ON utilisateur (google_id)');
    }

    public function down(Schema $schema): void
    {
        // Suppression de l'index
        $this->addSql('DROP INDEX UNIQ_1D1C63B376F5C865 ON utilisateur');
        
        // Suppression des colonnes
        $this->addSql('ALTER TABLE utilisateur DROP google_id, DROP oauth_provider');
    }
}
