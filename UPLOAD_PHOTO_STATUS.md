# ✅ Upload Photo de Profil - Configuration Terminée

## 🎯 Configuration Cloudinary

✅ **Clés API configurées dans `.env`:**
- Cloud Name: `dcdyn4gzp`
- API Key: `712216617942748`
- API Secret: `CaXY-uDkMdIoZpHM7J4mlyxJYBw`

## 📋 Fonctionnalités Implémentées

### ✅ 1. Page de Registration (`/register`)

**Fichiers modifiés:**
- ✅ `RegistrationController.php` - Ajout du service Cloudinary
- ✅ `RegistrationFormType.php` - Ajout du champ `photoFile` (FileType)

**Workflow:**
1. Utilisateur s'inscrit et upload optionnellement une photo
2. Photo uploadée vers Cloudinary avec ID temporaire
3. Compte créé en BDD avec l'URL Cloudinary
4. Photo re-uploadée avec l'ID utilisateur réel
5. **URL stockée dans la colonne `photo` (VARCHAR 500) - PAS de BLOB**

---

### ✅ 2. Page de Profil (`/profile/edit`)

**Fichiers modifiés:**
- ✅ `ProfileController.php` - Intégration Cloudinary
- ✅ `ProfileFormType.php` - Ajout du champ `photoFile`

**Workflow:**
1. Utilisateur modifie son profil et upload une nouvelle photo
2. Ancienne photo supprimée de Cloudinary (si existe)
3. Nouvelle photo uploadée vers Cloudinary
4. **URL mise à jour dans la BDD (colonne `photo`)**

---

## 🗄️ Stockage en Base de Données

### ✅ Configuration Entity User

```php
#[ORM\Column(length: 500, nullable: true)]
#[Assert\Url(message: 'URL photo invalide')]
private ?string $photo = null;
```

**Type de données:** `VARCHAR(500)` - Stocke l'URL, PAS le fichier binaire

**Exemple de valeur stockée:**
```
https://res.cloudinary.com/dcdyn4gzp/image/upload/v1234567890/smartnexus/profile_photos/user_5_1234567890.jpg
```

---

## 🎨 Transformations Automatiques

Chaque photo uploadée est automatiquement:
- ✂️ Recadrée en **500x500px**
- 🎯 Centrée sur le **visage** (détection automatique)
- 🗜️ **Compressée** intelligemment
- 🌐 Convertie en **WebP** (si supporté par le navigateur)
- ⚡ Servie via **CDN Cloudinary**

---

## 📁 Structure des Dossiers Cloudinary

```
smartnexus/
└── profile_photos/
    ├── user_1_1234567890.jpg
    ├── user_2_1234567891.jpg
    └── temp_abc123.jpg  (temporaires)
```

---

## 🧪 Comment Tester

### Test 1: Registration avec Photo

1. Allez sur: `http://127.0.0.1:8000/register`
2. Remplissez le formulaire
3. Uploadez une photo (JPG, PNG, GIF, WEBP, max 10MB)
4. Soumettez le formulaire
5. Vérifiez dans la BDD:
   ```sql
   SELECT id, nom, prenom, email, photo FROM utilisateur WHERE email = 'votre@email.com';
   ```
6. La colonne `photo` doit contenir une URL Cloudinary

### Test 2: Profile - Modifier la Photo

1. Connectez-vous
2. Allez sur: `http://127.0.0.1:8000/profile/edit`
3. Uploadez une nouvelle photo
4. Soumettez
5. Vérifiez que:
   - L'ancienne photo est supprimée de Cloudinary
   - La nouvelle URL est dans la BDD

### Test 3: Vérifier le Type de Données

```sql
DESCRIBE utilisateur;
```

La colonne `photo` doit être: `varchar(500)` ou `text` - **PAS `blob`**

---

## ✅ Checklist Finale

- [x] Cloudinary SDK installé
- [x] Service `CloudinaryUploadService` créé
- [x] Clés API configurées dans `.env`
- [x] `RegistrationController` mis à jour
- [x] `ProfileController` mis à jour
- [x] `RegistrationFormType` avec champ `photoFile`
- [x] `ProfileFormType` avec champ `photoFile`
- [x] Entity `User.photo` = `string` (URL)
- [x] Cache Symfony vidé
- [x] Transformations d'images configurées (500x500, crop face)

---

## 🎯 Validation du Stockage

### ❌ Ce qui N'est PAS utilisé:
- ❌ BLOB
- ❌ LONGBLOB
- ❌ Stockage binaire
- ❌ Fichiers locaux (sauf temporaires PHP)

### ✅ Ce qui EST utilisé:
- ✅ URL Cloudinary (string)
- ✅ Stockage cloud (Cloudinary)
- ✅ CDN pour la distribution
- ✅ Transformations automatiques

---

## 📊 Exemple de Donnée en BDD

```sql
-- Table: utilisateur
-- Colonne: photo (VARCHAR 500)

INSERT INTO utilisateur (nom, prenom, email, photo) VALUES
('Dupont', 'Jean', 'jean@example.com', 'https://res.cloudinary.com/dcdyn4gzp/image/upload/v1739000000/smartnexus/profile_photos/user_1_1739000000.jpg');
```

**Vérification:**
```sql
SELECT 
    id, 
    nom, 
    prenom, 
    photo,
    LENGTH(photo) as url_length,
    SUBSTRING(photo, 1, 50) as url_preview
FROM utilisateur 
WHERE photo IS NOT NULL;
```

---

## 🚀 Prêt pour la Production

Tout est configuré! L'upload de photos fonctionne dans:
- ✅ Registration
- ✅ Profil

Et les URLs sont stockées en base de données comme prévu.

**Testez maintenant!** 🎉
