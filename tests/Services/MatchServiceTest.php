<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 02/07/2018
 * Time: 16:22
 */

namespace App\Tests\Services;

use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Services\Enum\TournamentTypeEnum;
use App\Services\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MatchServiceTest extends KernelTestCase
{

    /* @var MatchService $matchSevice */
    private $matchSevice;
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;
    /* @var SessionInterface $session */
    private $session;
    /* @var  TFTournament $tournament */
    private $tournament;
    /* @var  TFUser $user */
    private $user;
    /* @var  TFUser $user2 */
    private $user2;

    protected function setUp() {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->session = $kernel->getContainer()->get('session');

        $this->matchSevice = new MatchService($this->entityManager, $this->session);
        $this->user = new TFUser();
        $this->user->setEmail('test@test.fr');
        $this->user->setCountry('FR');
        $this->user->setFirstname('test');
        $this->user->setLastname('test');
        $this->user->setNicknames(['test']);

        $this->user2 = new TFUser();
        $this->user2->setEmail('test2@test2.fr');
        $this->user2->setCountry('FR');
        $this->user2->setFirstname('test2');
        $this->user2->setLastname('test2');
        $this->user2->setNicknames(['test2']);

        $this->tournament = new TFTournament(TournamentTypeEnum::TYPE_SINGLE);
        $this->tournament->setOwner($this->user);
        $this->tournament->setName('Test');
        $this->tournament->setMaxParticipantNumber(16);

        $this->user->addTournaments($this->tournament);
        $this->user2->addTournaments($this->tournament);
        $this->tournament->addPlayer($this->user);
        $this->tournament->addPlayer($this->user2);
        $this->entityManager->persist($this->user);
        $this->entityManager->persist($this->user2);
        $this->entityManager->persist($this->tournament);
        $this->entityManager->flush();

        parent::setUp();
    }

    public function testGetMaxTurnInTournament()
    {
        $number = $this->matchSevice->getMaxTurnInTournament($this->tournament);
        $this->assertEquals(4, $number);
    }

    public function testGenerateMatches()
    {
        $this->matchSevice->generateMatches($this->tournament);
        $this->assertEquals(15, $this->tournament->getMatches()->count());
    }

    public function testGetMatchPerRound()
    {
        $this->matchSevice->generateMatches($this->tournament);
        $arr = $this->matchSevice->getMatchPerRound($this->tournament);
        $this->assertEquals(1, count($arr[0]));
        $this->assertEquals(2, count($arr[1]));
        $this->assertEquals(4, count($arr[2]));
        $this->assertEquals(8, count($arr[3]));
    }

    public function testGetMatchPerRoundNoMatchGenerated()
    {
        $arr = $this->matchSevice->getMatchPerRound($this->tournament);
        $this->assertEquals([], $arr);
    }

    public function tearDown()
    {
        $this->removeMatches($this->tournament, 3);
        $this->removeMatches($this->tournament, 2);
        $this->removeMatches($this->tournament, 1);
        $this->removeMatches($this->tournament, 0);
        $this->entityManager->remove($this->tournament);
        $this->entityManager->remove($this->user);
        $this->entityManager->remove($this->user2);

        $this->entityManager->flush();
        parent::tearDown();
    }

    private function removeMatches($tournament, $turn){
        foreach ($tournament->getMatches() as $match){
            if($match->getTurn() == $turn) {
                $this->entityManager->remove($match);
            }
        }
        $this->entityManager->flush();
    }
}
