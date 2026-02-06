# Gestion automatique du statut des réclamations

## 🔄 Fonctionnement

Le système met à jour **automatiquement** le statut des réclamations en fonction des réponses ajoutées.

### Règle de transition

Lorsqu'une réponse est ajoutée à une réclamation :

```
Si statut = "ouverte" → Passe automatiquement à "en_cours"
```

### Implémentation

Le changement automatique est géré dans le contrôleur `BackReclamationController` lors de l'ajout d'une réponse :

```php
if ($form->isSubmitted() && $form->isValid()) {
    // Mise à jour automatique du statut si la réclamation est "ouverte"
    if ($reclamation->getStatut() === 'ouverte') {
        $reclamation->setStatut('en_cours');
    }
    
    $entityManager->persist($reponse);
    $entityManager->flush();
    
    // ...
}
```

### Flux complet des statuts

```
┌─────────────┐
│   Création  │
│  (ouverte)  │
└──────┬──────┘
       │
       │ Première réponse ajoutée
       │ (automatique)
       ▼
┌─────────────┐
│  En cours   │◄─────┐
│ (en_cours)  │      │
└──────┬──────┘      │
       │             │
       │ Modification │ Autres réponses
       │ manuelle    │ (pas de changement)
       ▼             │
┌─────────────┐      │
│   Fermée    │──────┘
│  (fermee)   │
└─────────────┘
```

## 💡 Avantages

✅ **Suivi automatique** - Pas besoin de changer manuellement le statut
✅ **Cohérence** - Une réclamation avec réponse ne reste jamais "ouverte"
✅ **Traçabilité** - Indique clairement qu'une action a été prise
✅ **Gain de temps** - L'administrateur se concentre sur la réponse

## 📋 Comportements

| Situation | Statut initial | Action | Statut final |
|-----------|---------------|---------|--------------|
| Première réponse | ouverte | Ajout réponse | **en_cours** |
| Deuxième réponse | en_cours | Ajout réponse | en_cours (inchangé) |
| Réponse sur réclamation fermée | fermee | Ajout réponse | fermee (inchangé) |
| Modification manuelle | n'importe | Éditer réclamation | Selon choix admin |

## 🔧 Extension future possible

Pour mettre en place d'autres règles automatiques, vous pouvez modifier le contrôleur :

```php
// Exemple : Fermer automatiquement après 3 réponses
if ($reclamation->getReponses()->count() >= 3) {
    $reclamation->setStatut('fermee');
}

// Exemple : Réouvrir si nouvelle réponse après fermeture
if ($reclamation->getStatut() === 'fermee') {
    $reclamation->setStatut('en_cours');
}
```

## 🎯 Test

Pour tester la fonctionnalité :

1. Créez une réclamation avec statut "Ouverte"
2. Allez sur la page de détails
3. Ajoutez une réponse
4. Le statut passe automatiquement à "En cours"
5. Les badges de couleur se mettent à jour (vert → jaune)
