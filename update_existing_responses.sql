-- Mise à jour des réponses existantes pour définir auteur_type = 'admin'
-- Toutes les réponses créées avant la migration n'ont pas de auteur_type

UPDATE reponse SET auteur_type = 'admin' WHERE auteur_type IS NULL OR auteur_type = '';
