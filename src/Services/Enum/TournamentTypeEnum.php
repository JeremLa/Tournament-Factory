<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 21/06/2018
 * Time: 13:40
 */

namespace App\Services\Enum;


abstract class TournamentTypeEnum
{
    const TYPE_SINGLE = "single-elimination";


    /** @var array user friendly named type */
    protected static $typeName = [
        self::TYPE_SINGLE    => 'Elimination directe'
    ];


    /**
     * @param  string $typeShortName
     * @return string
     */
    public static function getTypeName($typeShortName)
    {
        if (!isset(static::$typeName[$typeShortName])) {
            return "Unknown type ($typeShortName)";
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