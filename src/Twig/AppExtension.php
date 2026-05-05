<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('prix_format', [$this, 'prixFormat']),
            new TwigFilter('statut_label', [$this, 'statutLabel']),
        ];
    }

    public function prixFormat(?float $prix): string
    {
        if ($prix === null || $prix == 0) return 'Gratuit';
        return number_format($prix, 2, ',', ' ') . ' €';
    }

    public function statutLabel(string $statut): string
    {
        return match($statut) {
            'brouillon' => '📝 Brouillon',
            'publie'    => '🟢 Publié',
            'complet'   => '🔴 Complet',
            'annule'    => '⚫ Annulé',
            default     => $statut,
        };
    }
}