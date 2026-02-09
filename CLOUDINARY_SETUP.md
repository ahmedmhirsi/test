# 📸 Configuration Cloudinary pour Upload Photos de Profil

## ✅ Installation Terminée

L'intégration Cloudinary a été installée avec succès dans votre application SmartNexus.

## 🔧 Configuration Requise

### 1. Créer un Compte Cloudinary Gratuit

1. Allez sur: **https://cloudinary.com/users/register/free**
2. Créez votre compte gratuit
3. Une fois connecté, accédez à votre **Dashboard**

### 2. Récupérer vos Clés API

Sur votre Dashboard Cloudinary, vous trouverez:
- **Cloud Name** (ex: `dxxxxxxxxx`)
- **API Key** (ex: `123456789012345`)
- **API Secret** (ex: `AbCdEfGhIjKlMnOpQrStUvWxYz`)

### 3. Configurer votre Application

Ouvrez le fichier `.env` et remplacez les valeurs par défaut:

```env
###> Cloudinary ###
CLOUDINARY_CLOUD_NAME=votre_cloud_name_ici
CLOUDINARY_API_KEY=votre_api_key_ici
CLOUDINARY_API_SECRET=votre_api_secret_ici
###< Cloudinary ###
```

**⚠️ Important:** Ne commitez JAMAIS le fichier `.env` avec vos vraies clés API!

## 🎯 Fonctionnalités Installées

### ✨ Service `CloudinaryUploadService`

**Méthodes disponibles:**

1. **`uploadProfilePhoto(UploadedFile $file, string $userId): string`**
   - Upload une photo de profil vers Cloudinary
   - Transformations automatiques:
     - Resize: 500x500 pixels
     - Crop: intelligent (focus sur le visage)
     - Qualité: automatique (optimisée)
     - Format: automatique (WebP si supporté)
   - Retourne: URL sécurisée de l'image

2. **`deleteImage(string $publicId): bool`**
   - Supprime une image de Cloudinary
   - Utile pour nettoyer les anciennes photos

3. **`validateImage(UploadedFile $file): array`**
   - Valide le fichier avant upload
   - Vérifie: format, taille, type MIME

4. **`extractPublicIdFromUrl(string $url): ?string`**
   - Extrait le public_id depuis une URL Cloudinary
   - Nécessaire pour la suppression

## 📋 Workflow d'Upload

### Dans ProfileController::edit()

```php
1. Utilisateur upload une photo
2. Validation automatique du fichier
3. Suppression de l'ancienne photo (si existe)
4. Upload vers Cloudinary avec transformations
5. Sauvegarde de l'URL dans la base de données
6. Flash message de confirmation
```

## 🎨 Transformations Automatiques

Chaque photo uploadée est automatiquement:
- ✂️ Recadrée en 500x500px
- 🎯 Centrée sur le visage (si détecté)
- 🗜️ Compressée intelligemment
- 🌐 Convertie en WebP (navigateurs modernes)
- ⚡ Servie via CDN global ultra-rapide

## 📦 Limites du Plan Gratuit

- 💾 **25 GB** de stockage
- 📡 **25 GB** de bande passante/mois
- 🔄 **25 crédits** de transformation/mois
- 🎯 **25,000 transformations** totales/mois

**Note:** Largement suffisant pour une application en développement!

## 🧪 Test de l'Intégration

1. Connectez-vous à votre application
2. Allez sur: **Profile > Modifier le profil**
3. Uploadez une photo (JPG, PNG, GIF, WEBP max 10MB)
4. Cliquez sur "Enregistrer"
5. Vérifiez que la photo apparaît bien

## 🐛 Résolution de Problèmes

### Erreur: "Cloudinary configuration not found"
➡️ Vérifiez que les clés sont bien configurées dans `.env`

### Erreur: "Invalid API key"
➡️ Vérifiez que vous avez copié les bonnes clés depuis le Dashboard Cloudinary

### Erreur: "Upload failed"
➡️ Vérifiez:
- Le fichier est bien une image
- La taille < 10 MB
- Format supporté (JPG, PNG, GIF, WEBP)

### Cache Symfony
Si les modifications ne sont pas prises en compte:
```bash
php bin/console cache:clear
```

## 🌐 Structure de l'URL Cloudinary

Format de l'URL générée:
```
https://res.cloudinary.com/{cloud_name}/image/upload/v{version}/smartnexus/profile_photos/user_{id}_{timestamp}.jpg
```

Cette URL est stockée dans la colonne `photo` de la table `utilisateur`.

## 📸 Exemples d'Utilisation

### Upload Simple
```php
$imageUrl = $cloudinaryService->uploadProfilePhoto($file, $user->getId());
$user->setPhoto($imageUrl);
```

### Avec Gestion d'Erreurs
```php
try {
    $imageUrl = $cloudinaryService->uploadProfilePhoto($file, $user->getId());
    $user->setPhoto($imageUrl);
    $this->addFlash('success', 'Photo mise à jour!');
} catch (\Exception $e) {
    $this->addFlash('danger', 'Erreur: ' . $e->getMessage());
}
```

### Supprimer l'Ancienne Photo
```php
$oldUrl = $user->getPhoto();
if ($oldUrl) {
    $publicId = $cloudinaryService->extractPublicIdFromUrl($oldUrl);
    if ($publicId) {
        $cloudinaryService->deleteImage($publicId);
    }
}
```

## ✅ Checklist de Configuration

- [ ] Compte Cloudinary créé
- [ ] Clés API récupérées
- [ ] Fichier `.env` configuré
- [ ] Cache Symfony vidé
- [ ] Test d'upload effectué
- [ ] Photo affichée correctement

## 📚 Documentation Complète

- **Cloudinary PHP SDK:** https://cloudinary.com/documentation/php_integration
- **Transformations d'Images:** https://cloudinary.com/documentation/image_transformations
- **Dashboard:** https://cloudinary.com/console

---

🎉 **Félicitations!** Votre système d'upload de photos avec Cloudinary est maintenant opérationnel!
