<?php

namespace App\Services\Enum;


abstract class TournamentTypeEnum
{
    const TYPE_SINGLE = "single-elimination";


    /**
     * @var array  $typeName
     */
    protected static $typeName = [
        self::TYPE_SINGLE    => 'Elimination directe'
    ];


    /**
     * @param  string $typeShortName
     * @return string|boolean
     */
    public static function getTypeName($typeShortName)
    {
        if (!isset(static::$typeName[$typeShortName])) {
            return false;
        }

        return static::$typeName[$typeShortName];
    }


    /**
     * @return array<string>
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_SINGLE
        ];
    }
}
