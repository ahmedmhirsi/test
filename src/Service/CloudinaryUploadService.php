<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;

class CloudinaryUploadService
{
    private Cloudinary $cloudinary;

    public function __construct(
        private LoggerInterface $logger,
        string $cloudName,
        string $apiKey,
        string $apiSecret
    ) {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
        ]);
    }

    /**
     * Upload une photo de profil vers Cloudinary et retourne l'URL
     * 
     * @param UploadedFile $file Le fichier uploadé
     * @param string $userId ID de l'utilisateur pour nommer le fichier
     * @return string URL sécurisée de l'image hébergée
     * @throws \RuntimeException Si l'upload échoue
     */
    public function uploadProfilePhoto(UploadedFile $file, string $userId): string
    {
        // Valider l'image
        $errors = $this->validateImage($file);
        if (!empty($errors)) {
            throw new \RuntimeException(implode(', ', $errors));
        }

        try {
            // Upload vers Cloudinary avec transformations automatiques
            $result = $this->cloudinary->uploadApi()->upload(
                $file->getPathname(),
                [
                    'folder' => 'smartnexus/profile_photos',
                    'public_id' => 'user_' . $userId . '_' . time(),
                    'overwrite' => true,
                    'transformation' => [
                        'width' => 500,
                        'height' => 500,
                        'crop' => 'fill',
                        'gravity' => 'face',
                        'quality' => 'auto',
                        'fetch_format' => 'auto',
                    ],
                ]
            );

            $imageUrl = $result['secure_url'];

            $this->logger->info('Image uploaded to Cloudinary successfully', [
                'url' => $imageUrl,
                'public_id' => $result['public_id'],
                'user_id' => $userId,
            ]);

            return $imageUrl;

        } catch (\Exception $e) {
            $this->logger->error('Cloudinary upload error', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'file' => $file->getClientOriginalName(),
            ]);
            throw new \RuntimeException('Erreur lors de l\'upload vers Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Supprime une image de Cloudinary
     * 
     * @param string $publicId ID public Cloudinary de l'image
     * @return bool True si suppression réussie
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);
            
            $success = $result['result'] === 'ok';
            
            if ($success) {
                $this->logger->info('Image deleted from Cloudinary', [
                    'public_id' => $publicId,
                ]);
            }
            
            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Cloudinary delete error', [
                'error' => $e->getMessage(),
                'public_id' => $publicId,
            ]);
            return false;
        }
    }

    /**
     * Extrait le public_id depuis une URL Cloudinary
     * 
     * @param string $url URL Cloudinary complète
     * @return string|null Le public_id ou null si non trouvé
     */
    public function extractPublicIdFromUrl(string $url): ?string
    {
        // Format: https://res.cloudinary.com/{cloud_name}/image/upload/v{version}/{folder}/{public_id}.{format}
        if (preg_match('/\/smartnexus\/profile_photos\/([^\.]+)/', $url, $matches)) {
            return 'smartnexus/profile_photos/' . $matches[1];
        }
        return null;
    }

    /**
     * Valide une image avant upload
     * 
     * @param UploadedFile $file Le fichier à valider
     * @param int $maxSize Taille maximale en octets (défaut: 10MB)
     * @return array Liste des erreurs (vide si valide)
     */
    public function validateImage(UploadedFile $file, int $maxSize = 10485760): array
    {
        $errors = [];

        // Types MIME autorisés
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();
        
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Format d\'image non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP';
        }

        // Taille maximale (10 MB par défaut)
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / 1048576;
            $errors[] = sprintf('La taille du fichier ne doit pas dépasser %.1f MB', $maxSizeMB);
        }

        // Vérifier que le fichier n'est pas vide
        if ($file->getSize() === 0) {
            $errors[] = 'Le fichier est vide';
        }

        return $errors;
    }

    /**
     * Obtient une URL transformée (resize, crop, etc.)
     * 
     * @param string $publicId Public ID Cloudinary
     * @param array $transformations Transformations à appliquer
     * @return string URL transformée
     */
    public function getTransformedUrl(string $publicId, array $transformations = []): string
    {
        try {
            return $this->cloudinary->image($publicId)
                ->resize($transformations['resize'] ?? null)
                ->toUrl();
        } catch (\Exception $e) {
            $this->logger->error('Error generating transformed URL', [
                'error' => $e->getMessage(),
                'public_id' => $publicId,
            ]);
            return '';
        }
    }
}
