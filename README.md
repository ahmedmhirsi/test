## Instructions pour démarrer l'application

### 1. Assurez-vous que XAMPP MySQL est démarré

Ouvrez le panneau de contrôle XAMPP et démarrez le service **MySQL**.

### 2. Créer la base de données et appliquer les migrations

Exécutez le script de configuration (double-cliquez sur le fichier ou lancez dans PowerShell):

```bash
.\setup_database.bat
```

OU exécutez manuellement les commandes suivantes:

```bash
# Créer la base de données
c:\xampp\php\php.exe bin/console doctrine:database:create --if-not-exists

# Générer les migrations
c:\xampp\php\php.exe bin/console doctrine:migrations:diff

# Appliquer les migrations
c:\xampp\php\php.exe bin/console doctrine:migrations:migrate --no-interaction

# Vérifier le schéma
c:\xampp\php\php.exe bin/console doctrine:schema:validate
```

### 3. Démarrer le serveur Symfony

```bash
c:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
```

### 4. Accéder à l'application

- **Back-office** (administration): http://127.0.0.1:8000/back/reclamation
- **Front-office** (utilisateurs): http://127.0.0.1:8000/front/reclamation

### 5. Vérifier dans phpMyAdmin

Ouvrez http://localhost/phpmyadmin et vérifiez que:
- La base `gestion_reclamations` existe
- Les tables `reclamation` et `reponse` sont créées
- La clé étrangère entre les tables est en place

## Structure des URLs

### Back-office
- `GET  /back/reclamation` - Liste des réclamations
- `GET  /back/reclamation/new` - Créer une réclamation
- `GET  /back/reclamation/{id}` - Détails d'une réclamation
- `GET  /back/reclamation/{id}/edit` - Modifier une réclamation
- `POST /back/reclamation/{id}/delete` - Supprimer une réclamation
- `GET  /back/reponse/{id}/edit` - Modifier une réponse
- `POST /back/reponse/{id}/delete` - Supprimer une réponse

### Front-office
- `GET  /front/reclamation` - Liste des réclamations
- `GET  /front/reclamation/new` - Créer une réclamation
- `GET  /front/reclamation/{id}` - Détails d'une réclamation (lecture seule)

## Fonctionnalités

✅ **Entités avec validation complète**
- Reclamation: titre, description, email, statut, priorité, dateCreation
- Reponse: message, auteur, dateReponse
- Relation OneToMany/ManyToOne avec CASCADE delete

✅ **CRUD complet pour le back-office**
- Gestion complète des réclamations
- Ajout/modification/suppression des réponses
- Filtrage par statut et priorité

✅ **Interface front-office simplifiée**
- Création de réclamations sans sélection de statut/priorité
- Consultation en lecture seule
- Interface utilisateur claire et accessible

✅ **Design premium avec Tailwind CSS**
- Interface moderne et responsive
- Dark mode support
- Animations et transitions fluides
- Material Icons pour une meilleure UX

✅ **Validation**
- Contraintes Symfony sur les entités
- Validation HTML5 côté client
- Messages d'erreur personnalisés en français
