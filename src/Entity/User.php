<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[UniqueEntity(fields: ['email'], message: 'Cet email existe déjà')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TotpTwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(
        message: 'Veuillez entrer une adresse email valide (ex: nom@exemple.com)',
        mode: 'strict'
    )]
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^(\+)?[0-9\s\-\.]{8,20}$/',
        message: 'Numéro de téléphone invalide (ex: +33 6 12 34 56 78 ou 0612345678)'
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/',
        message: 'Le nom ne doit contenir que des lettres, espaces, tirets et apostrophes'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/',
        message: 'Le prénom ne doit contenir que des lettres, espaces, tirets et apostrophes'
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'URL photo invalide')]
    private ?string $photo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'La biographie ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'expertise ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $expertise = null;

    // ========== CHAMPS OAUTH GOOGLE ==========

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $oauthProvider = null;

    // ========== CHAMPS EMAIL VERIFICATION ==========

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $verificationTokenExpiresAt = null;

    // ========== CHAMPS RESET PASSWORD ==========

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resetPasswordTokenExpiresAt = null;

    // ========== CHAMPS SMS VERIFICATION ==========

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $smsVerificationCode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $smsCodeExpiresAt = null;

    // ========== CHAMPS 2FA (TWO-FACTOR AUTHENTICATION) ==========

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $totpSecret = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $is2faEnabled = false;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $backupCodes = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $twoFactorConfirmedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $last2faCheckAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->roles = ['ROLE_USER'];
        // Ensure default values are set
        $this->isActive = true;
        $this->isVerified = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        if ($createdAt instanceof \DateTime) {
            $this->createdAt = \DateTimeImmutable::createFromMutable($createdAt);
        } else {
            $this->createdAt = $createdAt;
        }
        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): static
    {
        if ($lastLoginAt instanceof \DateTime) {
            $this->lastLoginAt = \DateTimeImmutable::createFromMutable($lastLoginAt);
        } else {
            $this->lastLoginAt = $lastLoginAt;
        }
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getExpertise(): ?string
    {
        return $this->expertise;
    }

    public function setExpertise(?string $expertise): static
    {
        $this->expertise = $expertise;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    // ========== MÉTHODES OAUTH GOOGLE ==========

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;
        return $this;
    }

    public function getOauthProvider(): ?string
    {
        return $this->oauthProvider;
    }

    public function setOauthProvider(?string $oauthProvider): self
    {
        $this->oauthProvider = $oauthProvider;
        return $this;
    }

    public function isOAuthUser(): bool
    {
        return $this->oauthProvider !== null;
    }

    public function isPasswordRequired(): bool
    {
        // Mot de passe non requis pour utilisateurs OAuth
        return !$this->isOAuthUser();
    }

    // ========== MÉTHODES EMAIL VERIFICATION ==========

    public function generateVerificationToken(): void
    {
        $this->verificationToken = bin2hex(random_bytes(32));
        $this->verificationTokenExpiresAt = new \DateTimeImmutable('+24 hours');
        $this->isVerified = false;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function isVerificationTokenValid(): bool
    {
        if ($this->verificationToken === null || $this->verificationTokenExpiresAt === null) {
            return false;
        }
        return $this->verificationTokenExpiresAt > new \DateTimeImmutable();
    }

    public function markAsVerified(): void
    {
        $this->isVerified = true;
        $this->verificationToken = null;
        $this->verificationTokenExpiresAt = null;
    }

    public function getVerificationTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->verificationTokenExpiresAt;
    }

    // ========== MÉTHODES RESET PASSWORD ==========

    public function generateResetPasswordToken(): void
    {
        $this->resetPasswordToken = bin2hex(random_bytes(32));
        $this->resetPasswordTokenExpiresAt = new \DateTimeImmutable('+1 hour');
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $token): self
    {
        $this->resetPasswordToken = $token;
        return $this;
    }

    public function isResetPasswordTokenValid(): bool
    {
        if ($this->resetPasswordToken === null || $this->resetPasswordTokenExpiresAt === null) {
            return false;
        }
        return $this->resetPasswordTokenExpiresAt > new \DateTimeImmutable();
    }

    public function clearResetPasswordToken(): void
    {
        $this->resetPasswordToken = null;
        $this->resetPasswordTokenExpiresAt = null;
    }

    public function getResetPasswordTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->resetPasswordTokenExpiresAt;
    }

    // ========== MÉTHODES SMS VERIFICATION ==========

    public function generateSmsCode(): string
    {
        $this->smsVerificationCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $this->smsCodeExpiresAt = new \DateTimeImmutable('+15 minutes');
        return $this->smsVerificationCode;
    }

    public function getSmsVerificationCode(): ?string
    {
        return $this->smsVerificationCode;
    }

    public function isSmsCodeValid(string $code): bool
    {
        if ($this->smsVerificationCode === null || $this->smsCodeExpiresAt === null) {
            return false;
        }
        
        if ($this->smsCodeExpiresAt < new \DateTimeImmutable()) {
            return false;
        }
        
        return $this->smsVerificationCode === $code;
    }

    public function clearSmsCode(): void
    {
        $this->smsVerificationCode = null;
        $this->smsCodeExpiresAt = null;
    }

    public function getSmsCodeExpiresAt(): ?\DateTimeImmutable
    {
        return $this->smsCodeExpiresAt;
    }

    // ========== MÉTHODES 2FA (TWO-FACTOR AUTHENTICATION) ==========

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->is2faEnabled && $this->totpSecret !== null;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        // Retourner la configuration si un secret existe, même si 2FA n'est pas encore activée
        // (nécessaire pour générer le QR code pendant le setup)
        if ($this->totpSecret === null) {
            return null;
        }

        return new TotpConfiguration(
            $this->totpSecret,
            TotpConfiguration::ALGORITHM_SHA1,
            30, // Period (30 seconds)
            6   // Digits (6-digit code)
        );
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): self
    {
        $this->totpSecret = $totpSecret;
        return $this;
    }

    public function is2faEnabled(): bool
    {
        return $this->is2faEnabled;
    }

    public function set2faEnabled(bool $is2faEnabled): self
    {
        $this->is2faEnabled = $is2faEnabled;
        return $this;
    }

    public function getBackupCodes(): ?array
    {
        return $this->backupCodes;
    }

    public function setBackupCodes(?array $backupCodes): self
    {
        $this->backupCodes = $backupCodes;
        return $this;
    }

    public function getTwoFactorConfirmedAt(): ?\DateTimeImmutable
    {
        return $this->twoFactorConfirmedAt;
    }

    public function setTwoFactorConfirmedAt(?\DateTimeImmutable $twoFactorConfirmedAt): self
    {
        $this->twoFactorConfirmedAt = $twoFactorConfirmedAt;
        return $this;
    }

    public function getLast2faCheckAt(): ?\DateTimeImmutable
    {
        return $this->last2faCheckAt;
    }

    public function setLast2faCheckAt(?\DateTimeImmutable $last2faCheckAt): self
    {
        $this->last2faCheckAt = $last2faCheckAt;
        return $this;
    }

    /**
     * Generate 10 backup codes (hashed with password_hash)
     */
    public function generateBackupCodes(): array
    {
        $plainCodes = [];
        $hashedCodes = [];

        for ($i = 0; $i < 10; $i++) {
            // Generate 8-character code: XXXX-XXXX
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4) . '-' . substr(bin2hex(random_bytes(4)), 0, 4));
            $plainCodes[] = $code;
            $hashedCodes[] = [
                'code' => password_hash($code, PASSWORD_BCRYPT),
                'used' => false,
                'usedAt' => null
            ];
        }

        $this->backupCodes = $hashedCodes;

        return $plainCodes;
    }

    /**
     * Validate a backup code and mark it as used
     */
    public function validateBackupCode(string $code): bool
    {
        if (!$this->backupCodes) {
            return false;
        }

        foreach ($this->backupCodes as $key => $backupCode) {
            if (!$backupCode['used'] && password_verify($code, $backupCode['code'])) {
                $this->backupCodes[$key]['used'] = true;
                $this->backupCodes[$key]['usedAt'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user needs to verify 2FA (every 7 days or on sensitive actions)
     */
    public function needs2faCheck(): bool
    {
        if (!$this->is2faEnabled) {
            return false;
        }

        // If never checked or last check was more than 7 days ago
        if ($this->last2faCheckAt === null) {
            return true;
        }

        $sevenDaysAgo = new \DateTimeImmutable('-7 days');
        return $this->last2faCheckAt < $sevenDaysAgo;
    }

    /**
     * Enable 2FA with secret
     */
    public function enableTwoFactorAuth(string $secret): self
    {
        $this->totpSecret = $secret;
        $this->is2faEnabled = true;
        $this->twoFactorConfirmedAt = new \DateTimeImmutable();
        $this->last2faCheckAt = new \DateTimeImmutable();
        return $this;
    }

    /**
     * Disable 2FA and clear all related data
     */
    public function disableTwoFactorAuth(): self
    {
        $this->totpSecret = null;
        $this->is2faEnabled = false;
        $this->backupCodes = null;
        $this->twoFactorConfirmedAt = null;
        $this->last2faCheckAt = null;
        return $this;
    }
}
