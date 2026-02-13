<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PremiumCard
{
    public string $title;
    public string $content;
    public string $icon = 'star';
    public string $accentColor = 'nexus-primary';
}
