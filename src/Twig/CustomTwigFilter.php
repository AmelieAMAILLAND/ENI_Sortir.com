<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomTwigFilter extends AbstractExtension
{
    public function getFilters() : array{
        return [
            new TwigFilter('mapStatusForTailwind', [$this, 'mapStatusForTailwind']),
        ];
    }

    public function mapStatusForTailwind(string $status) : string{
        $tailwindClass = '';
        switch ($status) {
            case 'canceled':
                $tailwindClass =  'bg-gray-500 opacity-70';
                break;
            case 'created':
                $tailwindClass =  'border border-slate-50 bg-blue-200';
                break;
            case 'full':
                $tailwindClass =  'border border-black-300 bg-stone-300';
                break;
            case 'closed':
                $tailwindClass =  'border border-red-300 bg-red-200';
                break;
            case 'archived':
                $tailwindClass =  'bg-gray-200 opacity-70';
                break;
            case 'in_progress':
                $tailwindClass =  'border border-green-500 bg-amber-50';
                break;
            case 'past':
                $tailwindClass =  'border border-slate-500 bg-gray-100';
                break;
            case 'published':
                $tailwindClass =  'border border-slate-50 bg-green-200';
                break;
        }
        return $tailwindClass;
    }
}