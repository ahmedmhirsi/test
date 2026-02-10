# SmartNexus Integration Guide

## Module Integration: Gestion des Tâches et Projets ↔ Gestion d'Utilisateurs

This document explains how the **Task & Project Management** module will integrate with the **User Management** module.

---

## Current Architecture

### User References (ID-based)

Instead of Doctrine relationships, we use integer IDs to reference users:

| Entity | Field | Purpose |
|--------|-------|---------|
| `Projet` | `managerId` | Project manager (chef de projet) |
| `Tache` | `assigneeId` | Task assignee |
| `JournalTemps` | `userId` | Time log author |

This design allows the modules to be developed and tested independently.

---

## Expected User Table Structure

The User Management module must have a `utilisateur` table with at minimum:

```sql
CREATE TABLE utilisateur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(180) UNIQUE NOT NULL,
    roles JSON NOT NULL,  -- e.g., ["ROLE_ADMIN"]
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    is_active TINYINT NOT NULL DEFAULT 1
);
```

---

## Role Definitions

| Role | Access Level |
|------|-------------|
| `ROLE_ADMIN` | Full access - manage all projects, users, settings |
| `ROLE_EMPLOYEE` | Work access - assigned to tasks, log time, view projects |
| `ROLE_CLIENT` | View access - see project progress (read-only) |
| `ROLE_CANDIDAT` | Limited - recruitment module only |

---

## Merge Steps

### Step 1: Copy Entities
Copy these entities to the merged project:
- `Projet.php`
- `Sprint.php`
- `Tache.php`
- `Jalon.php`
- `JournalTemps.php`

### Step 2: Replace UserProvider
Replace `MockUserProvider` with a real implementation:

```php
// src/Service/RealUserProvider.php
class RealUserProvider implements UserProviderInterface
{
    public function __construct(
        private UtilisateurRepository $userRepo
    ) {}

    public function getUserById(int $userId): ?array
    {
        $user = $this->userRepo->find($userId);
        if (!$user) return null;
        
        return [
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
    }
    // ... implement other methods
}
```

### Step 3: Update Service Configuration
In `services.yaml`:

```yaml
App\Service\UserProviderInterface:
    class: App\Service\RealUserProvider  # Changed from MockUserProvider
```

### Step 4: Enable Security (Optional)
Uncomment `#[IsGranted]` annotations in controllers.

---

## Testing the Integration

```bash
# Clear cache after merge
php bin/console cache:clear

# Verify services
php bin/console debug:autowiring UserProvider

# Run migrations if needed
php bin/console doctrine:schema:update --force
```

---

## Questions for User Module Developer

1. ✅ What roles exist? → `ROLE_ADMIN`, `ROLE_EMPLOYEE`, `ROLE_CLIENT`, `ROLE_CANDIDAT`
2. ⬜ Should we add `ROLE_CHEF_PROJET` for project managers?
3. ⬜ Is there a user avatar/photo field? → Yes, `photo` column exists
4. ⬜ How to get the current logged-in user ID?
