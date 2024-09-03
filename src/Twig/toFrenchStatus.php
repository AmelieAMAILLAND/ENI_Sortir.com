<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class toFrenchStatus extends AbstractExtension
{
    public function getFilters() : array{
        return [
            new TwigFilter('mapStatusToFrench', [$this, 'mapStatusToFrench']),
        ];
    }

    public function mapStatusToFrench(string $status) : string{
        $frenchStatus = '';
        switch ($status) {
            case 'canceled':
                $frenchStatus =  'Annulée';
                break;
            case 'created':
                $frenchStatus =  'En création';
                break;
            case 'in_progress':
                $frenchStatus =  'En cours';
                break;
            case 'past':
                $frenchStatus =  'Passée';
                break;
            case 'published':
                $frenchStatus =  'Ouverte';
                break;
            case 'full':
                $frenchStatus =  'Complète';
                break;
            case 'archived':
                $frenchStatus =  'Archivée';
                break;
            case 'closed':
                $frenchStatus =  'Fermée';
                break;
        }
        return $frenchStatus;
    }
}