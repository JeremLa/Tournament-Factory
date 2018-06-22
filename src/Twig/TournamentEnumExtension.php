<?php

namespace App\Twig;

use App\Services\Enum\TournamentTypeEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TournamentEnumExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('typeEnum', [$this, 'typeEnumFilter'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [$this, 'doSomething']),
        ];
    }

    public function typeEnumFilter($value)
    {
        return TournamentTypeEnum::getTypeName($value);
    }
}
