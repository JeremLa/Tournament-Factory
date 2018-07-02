<?php
namespace App\Tests\Services;

use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Services\Enum\TournamentTypeEnum;
use App\Services\TournamentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TournamentServiceTest extends KernelTestCase
{
    /* @var TournamentService $tournamentSevice */
    private $tournamentSevice;
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;
    /* @var  TFTournament $tournament */
    private $tournament;
    /* @var  TFUser $user */
    private $user;
    /* @var  TFUser $user2 */
    private $user2;

    protected function setUp() {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        $this->tournamentSevice = new TournamentService($this->entityManager);
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

        $this->entityManager->persist($this->user);
        $this->entityManager->persist($this->user2);
        $this->entityManager->persist($this->tournament);
        $this->entityManager->flush();

        parent::setUp();
    }

    public function testCheckTournamentNotExist()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->tournamentSevice->checkTournamentExist(999);
    }

    public function testCheckTournamentExist()
    {
        /** @var TFTournament $tournament */
      $tournament =  $this->tournamentSevice->checkTournamentExist($this->tournament->getId());
      $this->assertEquals($this->tournament, $tournament);
    }

    public function testAddTournamentParticipantCannotAdd()
    {
        $this->tournament->setMaxParticipantNumber(0);
        $bol = $this->tournamentSevice->addTournamentParticipant($this->user,$this->tournament);
        $this->assertFalse($bol);
        $this->assertEquals(0, $this->user->getTournaments()->count());
        $this->assertEquals(0, $this->tournament->getPlayers()->count());
    }

    public function testAddTournamentParticipant()
    {
        $bol = $this->tournamentSevice->addTournamentParticipant($this->user, $this->tournament);
        $this->assertTrue($bol);
        $this->assertEquals(1, $this->user->getTournaments()->count());
        $this->assertEquals(1, $this->tournament->getPlayers()->count());
    }

    public function testRemoveTournamentParticipant()
    {
        $this->tournament->addPlayer($this->user);
        $this->user->addTournaments($this->tournament);
        $bol = $this->tournamentSevice->removeTournamentParticipant($this->user,$this->tournament);
        $this->assertTrue($bol);
        $this->assertEquals(0, $this->user->getTournaments()->count());
        $this->assertEquals(0, $this->tournament->getPlayers()->count());
    }

    public function testUpdateTournamentParticipantNbMax()
    {
        $this->tournament->setMaxParticipantNumber(1);
        $array = [$this->user, new TFUser()];
        $bol = $this->tournamentSevice->updateTournamentParticipant($array, $this->tournament);
        $this->assertFalse($bol);
    }

    public function testUpdateTournamentParticipant()
    {
        $array = [$this->user->getEmail(), $this->user2->getEmail()];
        $bol = $this->tournamentSevice->updateTournamentParticipant($array, $this->tournament);
        $this->assertTrue($bol);
        $this->assertEquals(1, $this->user->getTournaments()->count());
        $this->assertEquals(1, $this->user2->getTournaments()->count());
        $this->assertEquals(2, $this->tournament->getPlayers()->count());

        $array = [$this->user->getEmail()];
        $this->tournamentSevice->updateTournamentParticipant($array, $this->tournament);
        $this->assertEquals(1, $this->user->getTournaments()->count());
        $this->assertEquals(0, $this->user2->getTournaments()->count());
        $this->assertEquals(1, $this->tournament->getPlayers()->count());
    }

    public function tearDown()
    {
        $this->entityManager->remove($this->user);
        $this->entityManager->remove($this->user2);
        $this->entityManager->remove($this->tournament);
        $this->entityManager->flush();
        parent::tearDown();
    }
}
