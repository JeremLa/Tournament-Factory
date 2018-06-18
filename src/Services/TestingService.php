<?php
/**
 * Created by PhpStorm.
 * User: Pouette
 * Date: 09/05/2018
 * Time: 11:30
 */

namespace App\Services;


class TestingService
{
    private $square;

    public function getSquareFaces()
    {
        $this->square = 4;
        return $this->square;
    }
}