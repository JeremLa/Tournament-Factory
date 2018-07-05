<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 02/07/2018
 * Time: 16:22
 */

namespace App\Tests\Services;

use App\Entity\TFMatch;
use App\Entity\TFTeam;
use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Form\Type\ScoreType;
use App\Services\Enum\TournamentStatusEnum;
use App\Services\Enum\TournamentTypeEnum;
use App\Services\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\VarDumper\VarDumper;

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
        foreach ($this->tournament->getMatches() as $match){
            if($match->getTurn() == $this->matchSevice->getMaxTurnInTournament($this->tournament)){
                $this->assertNotNull($match->getPlayers()->get(0));
                $this->assertNotNull($match->getPlayers()->get(1));
            }
        }
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

    public function testFindOr404Exception()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->matchSevice->findOr404(999);
    }

    public function testFindOr404()
    {
        $this->matchSevice->generateMatches($this->tournament);
        $matches = $this->tournament->getMatches();
        $match = $this->matchSevice->findOr404($matches[0]->getId());
        $this->assertEquals($matches[0], $match);
    }


    public function testAssignParticipant()
    {
        $this->tournament->setMaxParticipantNumber(2);
        $this->matchSevice->generateMatches($this->tournament,false);

        /** @var TFMatch $match */
        $match = $this->tournament->getMatches()->get(0);
        $players =  $match->getPlayers();
        $this->assertNull($players->get(0));
        $this->assertNull($players->get(1));
        $this->matchSevice->assignParticipants($this->tournament);

        $match = $this->tournament->getMatches()->get(0);
        $players =  $match->getPlayers();
        $this->assertNotNull($players->get(0));
        $this->assertNotNull($players->get(1));
    }

    public function testAssignPlayers()
    {
        $match = new TFMatch();
        $this->matchSevice->assignPlayers($match, $this->user, $this->user2);

        $this->assertContains($this->user, $match->getPlayers());
        $this->assertContains($this->user2, $match->getPlayers());
    }

    public function testAddTeams()
    {
        $match = new TFMatch();
        $team1 = new TFTeam();
        $team2 = new TFTeam();

        $this->matchSevice->addTeams($match,$team1, $team2);
        $this->assertContains($team2, $match->getTeams());
        $this->assertContains($team1, $match->getTeams());
    }

    public function testGetScoreForForm()
    {
        $match = new TFMatch();
        $result = $this->matchSevice->getScoreForForm($match);
        $this->assertEquals([], $result);

        $match->addPlayer($this->user);
        $result = $this->matchSevice->getScoreForForm($match);
        $this->assertEquals(['score1' => 0], $result);

        $match->addPlayer($this->user2);
        $result = $this->matchSevice->getScoreForForm($match);
        $this->assertEquals(['score1' => 0, 'score2' => 0], $result);

        $match->setScore($this->user->getId(), 5);
        $result = $this->matchSevice->getScoreForForm($match);
        $this->assertEquals(['score1' => 5, 'score2' => 0], $result);

        $match->setScore($this->user2->getId(), 4);
        $result = $this->matchSevice->getScoreForForm($match);
        $this->assertEquals(['score1' => 5, 'score2' => 4], $result);
    }

    public function testUpdateScore()
    {
        $match = new TFMatch();
        $next = new TFMatch();
        $match->setOver(true);
        $match->setTurn(1);
        $match->setNextMatch($next);
        $next->addPreviousMatch($match);
        $this->tournament->addMatch($match);
        $this->tournament->addMatch($next);
        $match->setTournament($this->tournament);
        $next->setTournament($this->tournament);
        $next->setTurn(0);
        $this->entityManager->persist($next);
        $this->matchSevice->updateScore($match, [0,0],false);
        self::assertEquals('match.singleElimination.over.noPlayers', $this->session->getFlashBag()->get('danger')[0]);

        $this->matchSevice->assignPlayers($match, $this->user, $this->user2);
        $this->matchSevice->updateScore($match, [0,0],false);
        self::assertEquals('match.singleElimination.over.update', $this->session->getFlashBag()->get('danger')[0]);

        $match->setOver(false);
        $this->matchSevice->updateScore($match, [0,0],false);
        self::assertEquals('match.singleElimination.over.notEqual', $this->session->getFlashBag()->get('warning')[0]);

        $this->matchSevice->updateScore($match, [-1,0],false);
        self::assertEquals('match.singleElimination.over.lowerThanZero', $this->session->getFlashBag()->get('warning')[0]);

        $this->matchSevice->updateScore($match, [-1,-1],false);
        self::assertEquals('match.singleElimination.over.notEqual', $this->session->getFlashBag()->get('warning')[0]);
        $this->matchSevice->updateScore($match, [-1,-1],false);
        self::assertEquals('match.singleElimination.over.lowerThanZero', $this->session->getFlashBag()->get('warning')[1]);

        $this->matchSevice->updateScore($match, [2,1],false);
        self::assertFalse($match->isOver());
        self::assertEmpty($next->getPlayers());


        $newMatch = $this->matchSevice->updateScore($match, [2,1],true);
        self::assertTrue($match->isOver());
        self::assertContains($this->user,$next->getPlayers());
        self::assertEquals($match, $newMatch);
    }

    public function testHasNoNegativeScore ()
    {
        $bol = $this->matchSevice->hasNoNegativeScore(0,2);
        $this->assertTrue($bol);

        $bol = $this->matchSevice->hasNoNegativeScore(-1,2);
        $this->assertFalse($bol);
    }

    public function testHasNoEquality ()
    {
        $bol = $this->matchSevice->hasNoEquality(0,2);
        $this->assertTrue($bol);

        $bol = $this->matchSevice->hasNoEquality(2,2);
        $this->assertFalse($bol);
    }

    public function testCanHaveEquality ()
    {
        $match = new TFMatch();
        $match->setTurn(0);
        $this->tournament->addMatch($match);
        $match->setTournament($this->tournament);
        $bol = $this->matchSevice->canHaveEquality($match);
        $this->assertFalse($bol);

        $this->tournament->setType(TournamentTypeEnum::TYPE_CHAMP);
        $bol = $this->matchSevice->canHaveEquality($match);
        $this->assertFalse($bol);
    }

    public function testCanBeUpdated ()
    {
        $match = new TFMatch();
        $match->setTurn(0);
        $this->matchSevice->canBeUpdated($match);
        self::assertEquals('match.singleElimination.over.noPlayers', $this->session->getFlashBag()->get('danger')[0]);

        $this->matchSevice->assignPlayers($match, $this->user, $this->user2);
        $match->setOver(true);
        $this->matchSevice->canBeUpdated($match);
        self::assertEquals('match.singleElimination.over.update', $this->session->getFlashBag()->get('danger')[0]);
    }

    public function testUpdateNextMatch ()
    {
        $match = new TFMatch();
        $match->setTurn(1);
        $this->tournament->addMatch($match);
        $match->setTournament($this->tournament);
        $match->setScore($this->user->getId(), 1);
        $match->setScore($this->user2->getId(), 0);
        $this->matchSevice->assignPlayers($match, $this->user, $this->user2);

        $this->matchSevice->updateNextMatch($match);
        self::assertEquals(TournamentStatusEnum::STATUS_FINISHED, $this->tournament->getStatus());

        $next = new TFMatch();
        $next->setTurn(0);
        $this->tournament->addMatch($next);
        $next->setTournament($this->tournament);
        $match->setNextMatch($next);
        $next->addPreviousMatch($match);
        $this->matchSevice->updateNextMatch($match);
        self::assertContains($this->user, $next->getPlayers());

    }

    public function testInitScore ()
    {
        $match = new TFMatch();
        $match->setTurn(0);
        $this->matchSevice->initScores($match);
        self::assertEquals([], $match->getScore());

        $this->matchSevice->assignPlayers($match, $this->user, $this->user2);
        $this->matchSevice->initScores($match);
        self::assertEquals([$this->user->getId() => 0, $this->user2->getId() => 0], $match->getScore());
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
