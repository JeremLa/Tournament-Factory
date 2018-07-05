<?php

namespace App\Twig;

use App\Services\Enum\TournamentStatusEnum;
use App\Services\Enum\TournamentTypeEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TournamentEnumExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('transEnum', [$this, 'transEnumFilter'], ['is_safe' => ['html']]),
            new TwigFilter('transClass', [$this, 'transClassFilter'], ['is_safe' => ['html']]),
            new TwigFilter('hint', [$this, 'hintFilter'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [$this, 'doSomething']),
        ];
    }

    public function transEnumFilter($value, $type = "")
    {
        switch ($type){
            case 'type' :
                $return = TournamentTypeEnum::getTypeName($value);
                break;
            case 'status' :
                $return = TournamentStatusEnum::getTypeName($value);
                break;
            default :
                $return = 'N/C';
                break;
        }
        return $return;
    }

    public function transClassFilter($value){
        $class = [  TournamentStatusEnum::STATUS_SETUP => 'fa fa-cogs',
                    TournamentStatusEnum::STATUS_STARTED => 'fa fa-play-circle',
                    TournamentStatusEnum::STATUS_FINISHED => 'fa fa-flag-checkered',
                    TournamentStatusEnum::STATUS_CANCELED => 'fa fa-ban'
            ];
        return $class[$value];
    }

    public function hintFilter($value){
        return TournamentStatusEnum::getTypeName($value);
    }

}
