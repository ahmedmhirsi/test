# Architecture SmartNexus AI

## 🏗️ Structure de l'Application

### BackOffice (Admin) 🔒
**Layout**: `templates/back_office/base.html.twig`
**Accès**: ROLE_ADMIN uniquement

**Dashboards**:
- **Admin Dashboard**: `/admin/dashboard`
  - Template: `templates/back_office/dashboard.html.twig`
  - Statistiques système
  - Gestion utilisateurs
  - Actions rapides

**Modules**:
- **Gestion Utilisateurs**: `/admin/user`
  - Controller: `src/Controller/Admin/UserController.php`
  - Templates: `templates/admin/user/`
  - Routes: `admin_user_index`, `admin_user_new`, `admin_user_edit`, `admin_user_show`, `admin_user_delete`

**Navigation BackOffice**:
- Dashboard Admin
- Gestion Utilisateurs
- Projets
- Candidatures
- Rapports
- Paramètres

---

### FrontOffice (Utilisateurs) 👥
**Layout**: `templates/front_office/base.html.twig`
**Accès**: ROLE_EMPLOYEE et ROLE_CANDIDAT

#### Dashboard Employé 💼
**Route**: `/employee/dashboard`
**Template**: `templates/dashboard/employee.html.twig`
**Fonctionnalités**:
- Mes Projets (8 actifs)
- Tâches en cours (12 tâches)
- Tâches complétées (45)
- Performance (94%)
- Liste des tâches prioritaires
- Activité récente
- Projets actifs avec progress

**Navigation**:
- Dashboard
- Mes Projets
- Mes Tâches
- Mon Profil

#### Dashboard Candidat 🎯
**Route**: `/candidat/dashboard`
**Template**: `templates/dashboard/candidat.html.twig`
**Fonctionnalités**:
- Mes Candidatures (5 total)
- En cours (3 candidatures)
- Entretiens à venir (1)
- Taux de succès (60%)
- Liste des candidatures avec statuts
- Prochain entretien
- Offres recommandées

**Navigation**:
- Mon Espace
- Mes Candidatures
- Offres d'emploi
- Mon Profil

---

## 📁 Structure des Fichiers

```
src/
├── Controller/
│   ├── Admin/
│   │   └── UserController.php          # CRUD Utilisateurs (Admin)
│   ├── DashboardController.php         # Gestion des dashboards
│   ├── RegistrationController.php      # Inscription
│   └── SecurityController.php          # Login/Logout

templates/
├── back_office/
│   ├── base.html.twig                  # Layout Admin avec sidebar
│   └── dashboard.html.twig             # Dashboard Admin
├── front_office/
│   └── base.html.twig                  # Layout FrontOffice (Employee/Candidat)
├── admin/
│   └── user/
│       ├── index.html.twig             # Liste utilisateurs
│       ├── new.html.twig               # Créer utilisateur
│       ├── edit.html.twig              # Modifier utilisateur
│       ├── show.html.twig              # Voir utilisateur
│       └── _form.html.twig             # Formulaire utilisateur
├── dashboard/
│   ├── employee.html.twig              # Dashboard Employé (FrontOffice)
│   └── candidat.html.twig              # Dashboard Candidat (FrontOffice)
├── security/
│   ├── login.html.twig                 # Page de connexion
│   └── register.html.twig              # Page d'inscription
└── base.html.twig                      # Layout de base
```

---

## 🔐 Rôles et Permissions

### ROLE_ADMIN
- ✅ Accès BackOffice complet
- ✅ Gestion des utilisateurs (CRUD)
- ✅ Voir toutes les statistiques
- ✅ Gérer les projets
- ✅ Gérer les candidatures
- ✅ Rapports et paramètres

### ROLE_EMPLOYEE
- ✅ Accès FrontOffice
- ✅ Dashboard Employé
- ✅ Gérer ses projets
- ✅ Gérer ses tâches
- ✅ Voir son profil
- ❌ Pas d'accès BackOffice

### ROLE_CANDIDAT
- ✅ Accès FrontOffice
- ✅ Dashboard Candidat
- ✅ Voir ses candidatures
- ✅ Postuler aux offres
- ✅ Gérer son profil
- ❌ Pas d'accès BackOffice

---

## 🎨 Design System

### BackOffice (Admin)
- **Sidebar fixe**: Navigation administrative
- **Top bar**: Breadcrumb, notifications, recherche
- **Couleurs**: Navy (#1A237E), Primary (#ffc105), Electric (#536DFE)
- **Cartes statistiques**: Gradients colorés par type
- **Tables**: Design moderne avec hover states

### FrontOffice (Users)
- **Sidebar personnalisée**: Navigation selon le rôle
- **Top bar**: Identique au BackOffice
- **Couleurs**: Mêmes que BackOffice
- **Cards interactives**: Progress bars, badges de statut
- **Design responsive**: Mobile-first

---

## 🚀 Routes Principales

### Authentification
```
GET  /                      → Redirection selon rôle
GET  /login                 → Page de connexion
POST /login                 → Traitement connexion
GET  /register              → Page d'inscription
POST /register              → Traitement inscription
GET  /logout                → Déconnexion
```

### BackOffice (Admin)
```
GET  /admin/dashboard                    → Dashboard Admin
GET  /admin/user                         → Liste utilisateurs
GET  /admin/user/new                     → Créer utilisateur
GET  /admin/user/{id}                    → Voir utilisateur
GET  /admin/user/{id}/edit               → Modifier utilisateur
POST /admin/user/{id}                    → Supprimer utilisateur
```

### FrontOffice (Utilisateurs)
```
GET  /dashboard                          → Redirection selon rôle
GET  /employee/dashboard                 → Dashboard Employé
GET  /candidat/dashboard                 → Dashboard Candidat
```

---

## 👥 Comptes de Test

### Admin (BackOffice)
- **Email**: admin@smartnexus.ai
- **Mot de passe**: admin123
- **Accès**: Dashboard Admin + Gestion complète

### Employé (FrontOffice)
- **Email**: employee@smartnexus.ai
- **Mot de passe**: employee123
- **Accès**: Dashboard Employé

### Candidat (FrontOffice)
- **Email**: candidat@smartnexus.ai
- **Mot de passe**: candidat123
- **Accès**: Dashboard Candidat

---

## 🛠️ Prochaines Étapes

### BackOffice
- [ ] Formulaires de création/édition utilisateur
- [ ] Gestion des projets
- [ ] Gestion des candidatures
- [ ] Système de rapports
- [ ] Paramètres système

### FrontOffice
- [ ] CRUD projets (Employés)
- [ ] CRUD tâches (Employés)
- [ ] CRUD candidatures (Candidats)
- [ ] Système de notifications
- [ ] Gestion du profil utilisateur
- [ ] Messagerie interne

### Sécurité
- [ ] OAuth (Google, Facebook)
- [ ] 2FA (TOTP + SMS)
- [ ] Vérification email
- [ ] Reset password

---

## 📝 Notes Techniques

- **Separation claire**: BackOffice (Admin) vs FrontOffice (Users)
- **Layouts dédiés**: `back_office/base.html.twig` vs `front_office/base.html.twig`
- **Routes préfixées**: `/admin/*` pour BackOffice
- **Sécurité**: `#[IsGranted('ROLE_ADMIN')]` sur tous les controllers Admin
- **Design cohérent**: Même design system, navigation différente
- **Pas de scroll**: Design fixe avec `overflow: hidden` sur body

---

## ⚙️ Commandes Utiles

```bash
# Démarrer le serveur
symfony server:start

# Nettoyer le cache
php bin/console cache:clear

# Voir les routes
php bin/console debug:router

# Voir les routes admin
php bin/console debug:router | grep admin

# Générer CRUD
php bin/console make:crud EntityName
```
