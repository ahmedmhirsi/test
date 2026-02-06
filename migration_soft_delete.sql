-- Migration SQL pour ajouter le champ deleted_by_client
-- Ce champ permet au client de "supprimer" (masquer) une réclamation de sa vue
-- sans la supprimer de la base de données (l'admin la voit toujours)

ALTER TABLE reclamation ADD COLUMN deleted_by_client TINYINT(1) DEFAULT 0 NOT NULL;
