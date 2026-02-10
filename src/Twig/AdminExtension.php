<?php

namespace App\Twig;

use App\Repository\ReclamationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    private $reclamationRepository;

    public function __construct(ReclamationRepository $reclamationRepository)
    {
        $this->reclamationRepository = $reclamationRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('untreated_reclamations_count', [$this, 'getUntreatedReclamationsCount']),
            new TwigFunction('recent_untreated_reclamations', [$this, 'getRecentUntreatedReclamations']),
        ];
    }

    public function getUntreatedReclamationsCount(): int
    {
        return $this->reclamationRepository->countEnCours();
    }

    public function getRecentUntreatedReclamations(int $limit = 5): array
    {
        return $this->reclamationRepository->findRecentEnCours($limit);
    }
}
