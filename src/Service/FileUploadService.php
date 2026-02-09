<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class FileUploadService
{
    private string $profilePhotosDirectory;
    private string $documentsDirectory;
    private ?string $n8nUploadWebhookUrl;

    public function __construct(
        private SluggerInterface $slugger,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        string $profilePhotosDirectory,
        string $documentsDirectory,
        ?string $n8nUploadWebhookUrl = null
    ) {
        $this->profilePhotosDirectory = $profilePhotosDirectory;
        $this->documentsDirectory = $documentsDirectory;
        $this->n8nUploadWebhookUrl = $n8nUploadWebhookUrl;
    }

    /**
     * Upload une photo de profil
     * 
     * @param UploadedFile $file Le fichier uploadé
     * @return string Le nom du fichier généré
     */
    public function uploadProfilePhoto(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Nettoyer le nom du fichier
        $safeFilename = $this->slugger->slug($originalFilename);
        
        // Générer un nom unique
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->profilePhotosDirectory, $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier: ' . $e->getMessage());
        }

        return $newFilename;
    }

    /**
     * Supprime une photo de profil
     * 
     * @param string $filename Le nom du fichier à supprimer
     * @return bool True si le fichier a été supprimé
     */
    public function deleteProfilePhoto(string $filename): bool
    {
        if (!$filename) {
            return false;
        }

        $filepath = $this->profilePhotosDirectory . '/' . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }

    /**
     * Valide le type et la taille d'une image
     * 
     * @param UploadedFile $file
     * @param int $maxSize Taille maximale en octets (défaut: 2MB)
     * @return array Liste des erreurs, vide si OK
     */
    public function validateImage(UploadedFile $file, int $maxSize = 2097152): array
    {
        $errors = [];

        // Vérifier le type MIME
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'Format d\'image non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP';
        }

        // Vérifier la taille
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / 1048576;
            $errors[] = sprintf('La taille du fichier ne doit pas dépasser %.1f MB', $maxSizeMB);
        }

        return $errors;
    }

    /**
     * Extrait le nom du fichier depuis un chemin complet
     * 
     * @param string|null $photoPath
     * @return string|null
     */
    public function extractFilename(?string $photoPath): ?string
    {
        if (!$photoPath) {
            return null;
        }

        // Si c'est déjà juste un nom de fichier
        if (strpos($photoPath, '/') === false) {
            return $photoPath;
        }

        // Extraire le nom du fichier depuis le chemin
        return basename($photoPath);
    }

    /**
     * Upload un document pour le RAG chatbot
     * 
     * @param UploadedFile $file Le fichier uploadé
     * @param string|null $description Description optionnelle du document
     * @return array ['filename' => string, 'originalName' => string, 'mimeType' => string, 'size' => int]
     */
    public function uploadDocument(UploadedFile $file, ?string $description = null): array
    {
        // Vérifier que le fichier est valide
        if (!$file->isValid()) {
            throw new \RuntimeException('Le fichier n\'est pas valide: ' . $file->getErrorMessage());
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Nettoyer le nom du fichier
        $safeFilename = $this->slugger->slug($originalFilename);
        
        // Générer un nom unique avec l'extension originale
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            // Créer le répertoire s'il n'existe pas
            if (!is_dir($this->documentsDirectory)) {
                mkdir($this->documentsDirectory, 0777, true);
            }

            // Déplacer le fichier immédiatement
            $file->move($this->documentsDirectory, $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier: ' . $e->getMessage());
        }

        return [
            'filename' => $newFilename,
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getMimeType() ?? $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Valide un document (tous formats acceptés)
     * 
     * @param UploadedFile $file
     * @param int $maxSize Taille maximale en octets (défaut: 50MB)
     * @return array Liste des erreurs, vide si OK
     */
    public function validateDocument(UploadedFile $file, int $maxSize = 52428800): array
    {
        $errors = [];

        // Vérifier la taille (50MB par défaut)
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
     * Envoie un document au webhook n8n pour traitement RAG
     * 
     * @param string $filename Nom du fichier sur le serveur
     * @param string $originalName Nom original du fichier
     * @param string|null $description Description du document
     * @return array ['success' => bool, 'message' => string, 'response' => mixed]
     */
    public function sendDocumentToN8n(string $filename, string $originalName, ?string $description = null): array
    {
        if (!$this->n8nUploadWebhookUrl) {
            $this->logger->warning('N8N upload webhook URL not configured');
            return [
                'success' => false,
                'message' => 'Webhook n8n non configuré',
                'response' => null
            ];
        }

        $filepath = $this->documentsDirectory . '/' . $filename;

        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'message' => 'Fichier non trouvé',
                'response' => null
            ];
        }

        try {
            $fileContent = file_get_contents($filepath);
            $base64Content = base64_encode($fileContent);
            $mimeType = mime_content_type($filepath);

            $response = $this->httpClient->request('POST', $this->n8nUploadWebhookUrl, [
                'json' => [
                    'filename' => $originalName,
                    'mimetype' => $mimeType,
                    'document_title' => pathinfo($originalName, PATHINFO_FILENAME),
                    'description' => $description ?? '',
                    'data' => $base64Content,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 60,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(false);

            $this->logger->info('Document sent to n8n', [
                'filename' => $originalName,
                'status' => $statusCode,
                'response' => $content
            ]);

            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'message' => $content['message'] ?? 'Document envoyé au chatbot',
                'response' => $content
            ];

        } catch (\Exception $e) {
            $this->logger->error('Error sending document to n8n', [
                'filename' => $originalName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * Supprime un document
     * 
     * @param string $filename Le nom du fichier à supprimer
     * @return bool True si le fichier a été supprimé
     */
    public function deleteDocument(string $filename): bool
    {
        if (!$filename) {
            return false;
        }

        $filepath = $this->documentsDirectory . '/' . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }
}
