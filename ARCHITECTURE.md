# Architecture : Back-Office vs Front-Office

## 🎯 Séparation des rôles

### 👥 Front-Office (Clients)
**URL** : `/front/reclamation`

**Permissions** :
- ✅ **Créer** des réclamations
- ✅ **Consulter** leurs réclamations
- ✅ **Consulter** les réponses de l'équipe
- ❌ **Modifier** les réclamations après création
- ❌ **Supprimer** les réclamations
- ❌ **Répondre** aux réclamations

### 🔐 Back-Office (Administrateurs)
**URL** : `/back/reclamation`

**Permissions** :
- ✅ **Consulter** toutes les réclamations
- ✅ **Ajouter des réponses** aux réclamations
- ✅ **Modifier les réponses** existantes
- ✅ **Supprimer des réponses**
- ✅ **Supprimer** des réclamations (si nécessaire)
- ❌ **Créer** des réclamations (réservé aux clients)
- ❌ **Modifier** les réclamations des clients

## 📋 Flux de travail

```
1. CLIENT (Front-Office)
   └─> Crée une réclamation
       ├─ Titre
       ├─ Description  
       └─ Email
       (Statut: "ouverte", Priorité: "moyenne" par défaut)

2. ADMIN (Back-Office)
   └─> Consulte la réclamation
       └─> Ajoute une réponse
           └─> Statut passe automatiquement à "en_cours"

3. CLIENT (Front-Office)
   └─> Consulte la réponse

4. ADMIN (Back-Office)
   └─> Peut ajouter d'autres réponses
       └─> Peut marquer manuellement comme "fermée" via édition
```

## 🛡️ Pourquoi cette séparation ?

### Intégrité des données
- Les réclamations représentent les **problèmes réels des clients**
- Elles ne doivent pas être modifiées par l'équipe support
- Cela garantit la **traçabilité** et l'**authenticité**

### Responsabilités claires
- **Clients** : Décrivent leur problème
- **Admins** : Répondent et traitent le problème

### Audit et historique
- Aucune modification possible = historique fiable
- Utile pour les analyses et statistiques
- Protection contre les modifications abusives

## 📁 Structure des contrôleurs

### FrontReclamationController
```php
- index()    // Liste des réclamations du client
- new()      // Création d'une réclamation ✅ SEULE CRÉATION
- show()     // Détails en lecture seule
```

### BackReclamationController
```php
- index()    // Liste de toutes les réclamations avec filtres
- show()     // Détails + formulaire d'ajout de réponse
- delete()   // Suppression (cas exceptionnels)
```

### BackReponseController
```php
- edit()     // Modification d'une réponse
- delete()   // Suppression d'une réponse
```

## ⚠️ Important

Les réclamations sont **créées uniquement par les clients** pour garantir :
- L'authenticité des demandes
- La traçabilité complète
- L'impossibilité de manipulation par l'équipe
- Un historique fiable pour les audits

L'équipe support ne peut que **répondre** et **gérer** les réclamations existantes.
