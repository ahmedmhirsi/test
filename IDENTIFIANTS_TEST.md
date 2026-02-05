# SmartNexus AI - Système de Gestion des Utilisateurs

## 🚀 Démarrage Rapide

### Lancer le serveur Symfony
```bash
symfony server:start
```

Le serveur sera accessible sur: http://127.0.0.1:8000

## 👥 Comptes de Test

### Admin
- **Email**: admin@smartnexus.ai
- **Mot de passe**: admin123
- **Accès**: Dashboard Admin avec gestion complète des utilisateurs

### Employé
- **Email**: employee@smartnexus.ai
- **Mot de passe**: employee123
- **Accès**: Dashboard Employé avec gestion des projets et tâches

### Candidat
- **Email**: candidat@smartnexus.ai  
- **Mot de passe**: candidat123
- **Accès**: Dashboard Candidat avec suivi des candidatures

## 📋 Fonctionnalités Implémentées

### Phase 1 - Authentification de Base ✅
- ✅ Inscription avec rôles (Candidat, Employé)
- ✅ Connexion sécurisée avec CSRF
- ✅ Remember Me (30 jours)
- ✅ Redirections basées sur les rôles
- ✅ Dashboards séparés par rôle
- ✅ Templates modernes avec Tailwind CSS
- ✅ Pas de scroll sur les pages (design fixe)

### Templates Disponibles
- **Login**: Page de connexion avec design moderne (mesh-gradient, Material Icons)
- **Register**: Formulaire d'inscription avec validation de mot de passe répété
- **Dashboard Admin**: Vue d'ensemble avec stats, utilisateurs récents
- **Dashboard Employé**: Gestion des tâches et projets
- **Dashboard Candidat**: Suivi des candidatures et offres recommandées

## 🎨 Design System

### Couleurs
- **Primary**: #ffc105 (Jaune/Or)
- **Navy**: #1A237E (Bleu Marine)
- **Electric**: #536DFE (Bleu Électrique)
- **Background Light**: #f8f8f5

### Polices
- **Display**: Manrope (Titres)
- **Body**: Open Sans (Texte)
- **Title**: Montserrat (Headers)

### Icônes
- Material Symbols Outlined

## 📁 Structure des Fichiers

```
templates/
├── back_office/
│   ├── base.html.twig          # Layout principal back-office (sidebar + nav)
│   └── dashboard.html.twig     # Dashboard admin
├── dashboard/
│   ├── employee.html.twig      # Dashboard employé
│   └── candidat.html.twig      # Dashboard candidat
├── security/
│   └── login.html.twig         # Page de connexion
└── registration/
    └── register.html.twig      # Page d'inscription
```

## 🔐 Sécurité

- Mots de passe hashés avec Bcrypt (cost 13)
- Protection CSRF sur tous les formulaires
- Validation côté serveur (pas de HTML5)
- Session sécurisée avec Remember Me

## 🎯 Prochaines Étapes

### Step 3: CRUD Utilisateurs
- Générer CRUD avec `make:crud User`
- Créer interface admin de gestion des utilisateurs
- Ajouter recherche/filtres
- Implémenter pagination

### Phase 2: Sécurité Avancée
- OAuth (Google, Facebook)
- 2FA (TOTP + SMS)
- Vérification email activation
- Réinitialisation mot de passe

## 🛠️ Commandes Utiles

```bash
# Nettoyer le cache
php bin/console cache:clear

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Hasher un mot de passe
php bin/console security:hash-password

# Requête SQL directe
php bin/console doctrine:query:sql "SELECT * FROM utilisateur"
```

## 📝 Notes Techniques

- **Symfony**: 6.4
- **PHP**: 8.2.12
- **MySQL**: 8.0.32
- **Database**: smartnexus
- **CSS Framework**: Tailwind CSS (via CDN)
- **Form Type**: Repeated pour confirmation mot de passe (min 8 caractères)

## ⚠️ Contraintes Respectées

- ❌ Pas de FOSUserBundle
- ❌ Pas de EasyAdmin
- ✅ Images stockées comme URLs (pas BLOBs)
- ✅ Validation PHP uniquement (novalidate sur forms)
- ✅ Maximum 2-3 tables par module

## 🌐 URLs Principales

- **Page d'accueil**: http://127.0.0.1:8000/
- **Login**: http://127.0.0.1:8000/login
- **Register**: http://127.0.0.1:8000/register
- **Dashboard Admin**: http://127.0.0.1:8000/admin/dashboard
- **Dashboard Employé**: http://127.0.0.1:8000/employee/dashboard
- **Dashboard Candidat**: http://127.0.0.1:8000/candidat/dashboard
