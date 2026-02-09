<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TwoFactorController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TotpAuthenticatorInterface $totpAuthenticator
    ) {
    }

    // ========== ROUTES POUR LE BUNDLE SCHEB 2FA ==========
    // Ces routes sont utilisées par le bundle lors du login

    /**
     * Page de vérification 2FA lors du login
     * Affichée automatiquement par le bundle scheb quand l'utilisateur se connecte avec 2FA activée
     */
    #[Route('/2fa', name: '2fa_login')]
    public function login2fa(): Response
    {
        return $this->render('security/2fa_login.html.twig');
    }

    /**
     * Route de vérification du code 2FA
     * Gérée automatiquement par le bundle scheb - ne pas implémenter de logique ici
     */
    #[Route('/2fa_check', name: '2fa_login_check')]
    public function check2fa(): void
    {
        // Cette méthode ne sera jamais appelée - le bundle scheb intercepte la requête
        throw new \LogicException('This should never be reached');
    }

    // ========== GESTION 2FA DANS LE PROFIL ==========

    #[Route('/profile/2fa', name: 'app_2fa_settings')]
    #[IsGranted('ROLE_USER')]
    public function settings(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('profile/2fa_settings.html.twig', [
            'user' => $user,
            'is2faEnabled' => $user->is2faEnabled(),
            'backupCodesCount' => $user->getBackupCodes() ? count(array_filter($user->getBackupCodes(), fn($code) => !$code['used'])) : 0,
        ]);
    }

    #[Route('/profile/2fa/enable', name: 'app_2fa_enable')]
    #[IsGranted('ROLE_USER')]
    public function enable(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->is2faEnabled()) {
            $this->addFlash('warning', 'L\'authentification à deux facteurs est déjà activée.');
            return $this->redirectToRoute('app_2fa_settings');
        }

        // Generate TOTP secret
        $secret = $this->totpAuthenticator->generateSecret();
        $user->setTotpSecret($secret);
        $this->entityManager->flush();

        // Generate QR code (using SVG - no GD extension required)
        $qrCodeContent = $this->totpAuthenticator->getQRContent($user);
        
        $result = new Builder(
            writer: new SvgWriter(),
            writerOptions: [],
            validateResult: false,
            data: $qrCodeContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $qrCodeDataUri = $result->build()->getDataUri();

        return $this->render('profile/2fa_enable.html.twig', [
            'secret' => $secret,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);
    }

    #[Route('/profile/2fa/confirm', name: 'app_2fa_confirm', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function confirm(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $code = $request->request->get('code');

        if (!$code || !$this->totpAuthenticator->checkCode($user, $code)) {
            $this->addFlash('error', 'Code invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_2fa_enable');
        }

        // Enable 2FA
        $user->set2faEnabled(true);
        $user->setTwoFactorConfirmedAt(new \DateTimeImmutable());
        $user->setLast2faCheckAt(new \DateTimeImmutable());

        // Generate backup codes
        $plainCodes = $user->generateBackupCodes();

        $this->entityManager->flush();

        $this->addFlash('success', 'Authentification à deux facteurs activée avec succès !');

        return $this->render('profile/2fa_backup_codes.html.twig', [
            'backupCodes' => $plainCodes,
            'isFirstTime' => true,
        ]);
    }

    #[Route('/profile/2fa/disable', name: 'app_2fa_disable', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function disable(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->is2faEnabled()) {
            $this->addFlash('warning', 'L\'authentification à deux facteurs n\'est pas activée.');
            return $this->redirectToRoute('app_2fa_settings');
        }

        // Verify current password for security
        $password = $request->request->get('password');
        if (!$password || !password_verify($password, $user->getPassword())) {
            $this->addFlash('error', 'Mot de passe incorrect.');
            return $this->redirectToRoute('app_2fa_settings');
        }

        // Disable 2FA
        $user->disableTwoFactorAuth();
        $this->entityManager->flush();

        $this->addFlash('success', 'Authentification à deux facteurs désactivée.');

        return $this->redirectToRoute('app_2fa_settings');
    }

    #[Route('/profile/2fa/backup-codes', name: 'app_2fa_backup_codes')]
    #[IsGranted('ROLE_USER')]
    public function backupCodes(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->is2faEnabled()) {
            $this->addFlash('warning', 'L\'authentification à deux facteurs n\'est pas activée.');
            return $this->redirectToRoute('app_2fa_settings');
        }

        $backupCodes = $user->getBackupCodes() ?? [];

        return $this->render('profile/2fa_backup_codes_view.html.twig', [
            'backupCodes' => $backupCodes,
        ]);
    }

    #[Route('/profile/2fa/backup-codes/regenerate', name: 'app_2fa_backup_codes_regenerate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function regenerateBackupCodes(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->is2faEnabled()) {
            $this->addFlash('warning', 'L\'authentification à deux facteurs n\'est pas activée.');
            return $this->redirectToRoute('app_2fa_settings');
        }

        // Verify current password for security
        $password = $request->request->get('password');
        if (!$password || !password_verify($password, $user->getPassword())) {
            $this->addFlash('error', 'Mot de passe incorrect.');
            return $this->redirectToRoute('app_2fa_backup_codes');
        }

        // Regenerate backup codes
        $plainCodes = $user->generateBackupCodes();
        $this->entityManager->flush();

        $this->addFlash('success', 'Nouveaux codes de secours générés !');

        return $this->render('profile/2fa_backup_codes.html.twig', [
            'backupCodes' => $plainCodes,
            'isFirstTime' => false,
        ]);
    }
}
