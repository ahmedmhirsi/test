<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour supprimer la colonne priorite et mettre à jour les statuts
 */
final class Version20260208214700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprime la colonne priorite et met à jour les statuts (ouverte -> en_cours, ajoute repondu)';
    }

    public function up(Schema $schema): void
    {
        // Mettre à jour toutes les réclamations avec statut 'ouverte' vers 'en_cours'
        $this->addSql("UPDATE reclamation SET statut = 'en_cours' WHERE statut = 'ouverte'");

        // Supprimer la colonne priorite
        $this->addSql('ALTER TABLE reclamation DROP priorite');
    }

    public function down(Schema $schema): void
    {
        // Recréer la colonne priorite avec une valeur par défaut
        $this->addSql("ALTER TABLE reclamation ADD priorite VARCHAR(50) NOT NULL DEFAULT 'moyenne'");

        // Remettre les statuts 'en_cours' à 'ouverte' (seulement ceux sans réponse)
        $this->addSql("UPDATE reclamation SET statut = 'ouverte' WHERE statut = 'en_cours'");
    }
}
