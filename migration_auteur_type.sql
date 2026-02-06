-- Migration SQL pour ajouter le champ auteur_type

ALTER TABLE reponse ADD COLUMN auteur_type VARCHAR(20) DEFAULT 'admin' NOT NULL;

-- Pour les réponses existantes, définir admin par défaut
UPDATE reponse SET auteur_type = 'admin' WHERE auteur_type IS NULL OR auteur_type = '';
