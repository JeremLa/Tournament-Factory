<?php

namespace App\Services\Enum;


abstract class TournamentStatusEnum
{

    const STATUS_SETUP = "setup";
    const STATUS_STARTED = "started";
    const STATUS_FINISHED = "finished";
    const STATUS_CANCELED = "canceled";

    /* @var array  $typeName */
    protected static $typeName = [
        self::STATUS_SETUP    => 'En préparation',
        self::STATUS_STARTED   => 'En cours',
        self::STATUS_FINISHED    => 'Terminé',
        self::STATUS_CANCELED    => 'Annulé',
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
            self::STATUS_SETUP,
            self::STATUS_STARTED,
            self::STATUS_FINISHED,
            self::STATUS_CANCELED
        ];
    }
}