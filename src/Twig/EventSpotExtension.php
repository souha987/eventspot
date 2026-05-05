<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EventSpotExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_ago', [$this, 'timeAgo']),
            new TwigFilter('price_format', [$this, 'priceFormat']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('capacity_badge', [$this, 'capacityBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function timeAgo(?\DateTimeInterface $date): string
    {
        if (!$date) return '';
        $now  = new \DateTime();
        $diff = $now->diff($date);
        if ($diff->y > 0) return 'il y a ' . $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) return 'il y a ' . $diff->m . ' mois';
        if ($diff->d > 0) return 'il y a ' . $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        if ($diff->h > 0) return 'il y a ' . $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        if ($diff->i > 0) return 'il y a ' . $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        return 'à l\'instant';
    }

    public function priceFormat(?float $prix): string
    {
        if ($prix === null || $prix == 0) return 'Gratuit 🎉';
        return number_format($prix, 2, ',', ' ') . ' €';
    }

    public function capacityBadge(int $nbInscrits, int $capaciteMax): string
    {
        if ($capaciteMax === 0) return '';
        $taux = ($nbInscrits / $capaciteMax) * 100;
        if ($taux >= 100) return '<span class="badge bg-dark">Complet</span>';
        if ($taux >= 80)  return '<span class="badge bg-danger">Dernières places</span>';
        if ($taux >= 50)  return '<span class="badge bg-warning text-dark">Places limitées</span>';
        return '<span class="badge bg-success">Places disponibles</span>';
    }
}