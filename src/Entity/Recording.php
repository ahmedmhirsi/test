<?php

namespace App\Entity;

use App\Repository\RecordingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecordingRepository::class)]
#[ORM\Table(name: 'recording')]
#[ORM\HasLifecycleCallbacks]
class Recording
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_recording')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file_path = null;

    #[ORM\Column(length: 50)]
    private ?string $file_type = 'video/webm'; // video/webm, audio/webm

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?int $file_size = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $duration = null; // Duration in seconds

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $transcription = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'recording'; // recording, processing, completed, failed

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\ManyToOne(targetEntity: Meeting::class)]
    #[ORM\JoinColumn(name: 'id_meeting', referencedColumnName: 'id_meeting', nullable: true)]
    private ?Meeting $meeting = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $is_public = false;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(?string $file_path): static
    {
        $this->file_path = $file_path;
        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->file_type;
    }

    public function setFileType(string $file_type): static
    {
        $this->file_type = $file_type;
        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    public function setFileSize(?int $file_size): static
    {
        $this->file_size = $file_size;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getTranscription(): ?string
    {
        return $this->transcription;
    }

    public function setTranscription(?string $transcription): static
    {
        $this->transcription = $transcription;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getMeeting(): ?Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(?Meeting $meeting): static
    {
        $this->meeting = $meeting;
        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->is_public;
    }

    public function setIsPublic(bool $is_public): static
    {
        $this->is_public = $is_public;
        return $this;
    }

    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return '0:00';
        }
        
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getFormattedFileSize(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
