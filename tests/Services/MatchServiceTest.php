<?php
/**
 * Created by PhpStorm.
 * User: Pouette
 * Date: 28/06/2018
 * Time: 20:40
 */

namespace App\Tests\Services;

use App\Services\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MatchServiceTest extends KernelTestCase
{
    /* @var MatchService $service */
    private $service;
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function setUp()
    {
        $kernel = self::$kernel;
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->service = new MatchService($this->entityManager);
        parent::setUp();
    }

    public function testAssignPlayers()
    {
    }

    public function testGetMaxTurnInTournament()
    {

    }

    public function testGenerateMatches()
    {

    }

    public function testAddTeams()
    {

    }

    public function testAssignParticipants()
    {

    }

    public function testGetMatchPerRound()
    {

    }
}
