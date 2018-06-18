<?php
namespace App\Tests\Services;


use App\Services\TestingService;
use PHPUnit\Framework\TestCase;

class TestingServiceTest extends TestCase
{
    public function testSquareHasFourFaces () {
        $testingService = new TestingService();

        $square = $testingService->getSquareFaces();

        $this->assertEquals(4, $square);
    }
}