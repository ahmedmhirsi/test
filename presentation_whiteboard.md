# Guide de Soutenance : Architecture du Tableau Blanc (tldraw)

Ce document vous aide à expliquer vos choix techniques lors de votre présentation universitaire.

## 1. Pourquoi tldraw ? (Choix Académique)
- **Open-Source & Moderne** : Contrairement à Fabric.js (plus ancien) ou Excalidraw (moins flexible en intégration), tldraw offre une API moderne et des performances optimales.
- **Simplicité d'Intégration** : Il s'intègre parfaitement dans un environnement React sans nécessiter de serveur de collaboration complexe pour un MVP.
- **UX Professionnelle** : L'interface est "Startup-Ready", ce qui valorise votre projet.

## 2. Architecture de Persistance Hybride
L'application utilise deux couches de sauvegarde pour une robustesse maximale :
1. **Couche Locale (Browser Storage)** : Grâce à la `persistenceKey`, les données survivent à un rafraîchissement même sans connexion internet et se synchronisent instantanément entre deux onglets du même navigateur.
2. **Couche Distante (Database Symfony)** : 
   - Au chargement, le composant interroge l'API Symfony (`/load`).
   - À chaque modification, une sauvegarde automatique (déclenchée après 1.5s d'inactivité) envoie le nouvel état au serveur (`/save`).

## 3. Perspectives d'Évolution
Si le projet devait passer à l'échelle (Production) :
- **Temps Réel (Multi-utilisateurs)** : On pourrait intégrer **Liveblocks** ou **WebSockets (Mercure)** pour synchroniser les snapshots entre plusieurs navigateurs instantanément.
- **Export Avancé** : Utiliser des Workers pour générer des images HD ou des PDF vectoriels à partir du JSON.
- **Historique (Undo/Redo distant)** : Stocker chaque version du board pour permettre un retour en arrière complet.

## Points Clés à retenir pour le jury :
- **Sécurité** : La sauvegarde est protégée par le système de sécurité de Symfony (Voters).
- **Performance** : Utilisation du "Debouncing" pour ne pas surcharger le serveur à chaque trait de crayon.
- **Maintenabilité** : Séparation claire entre la logique de dessin (tldraw) et la persistance (Symfony API).
