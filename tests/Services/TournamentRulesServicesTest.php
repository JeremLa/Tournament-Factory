<?php
namespace App\Tests\Services;

use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Services\Enum\TournamentStatusEnum;
use App\Services\TournamentRulesServices;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TournamentRulesServicesTest extends KernelTestCase
{
    /* @var TournamentRulesServices $service*/
    private $service;
    /* @var TFTournament $tournament */
    private $tournament;

    public function setUp()
    {
        $kernel = self::bootKernel();
        $this->service = new TournamentRulesServices($kernel->getContainer()->get('session'));
        $this->tournament = new TFTournament('single-elimination');
        parent::setUp();
    }

    public function testIsUpdatableNBMax()
    {
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->addPlayer(new TFUser());

        $this->assertTrue($this->service->isUpdatableNBMax($this->tournament, 3));
        $this->assertFalse($this->service->isUpdatableNBMax($this->tournament, 1));
    }

    public function testIsParticipantMaxed()
    {
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->setMaxParticipantNumber(2);

        $this->assertTrue($this->service->isParticipantMaxed($this->tournament));

        $this->tournament->setMaxParticipantNumber(3);

        $this->assertFalse($this->service->isParticipantMaxed($this->tournament));
    }

    public function testCanBeCancelled()
    {
        $owner = new TFUser();
        $this->tournament->setOwner($owner);
        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);

        $this->assertTrue($this->service->canBeCancelled($this->tournament, $owner));

        $randomUser = new TFUser();

        $this->assertFalse($this->service->canBeCancelled($this->tournament, $randomUser));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);

        $this->assertFalse($this->service->canBeCancelled($this->tournament, $owner));
        $this->assertFalse($this->service->canBeCancelled($this->tournament, $randomUser));
    }

    public function testCanBeDeleted()
    {
        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);

        $this->assertTrue($this->service->canBeDeleted($this->tournament));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);

        $this->assertFalse($this->service->canBeDeleted($this->tournament));
    }

    public function testHasMinParticipantRequired()
    {
        $this->tournament->setMaxParticipantNumber(5);

        $this->tournament->addPlayer(new TFUser());
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->addPlayer(new TFUser());

        $this->assertFalse($this->service->hasMinParticipantRequired($this->tournament));

        $this->tournament->setMaxParticipantNumber(1);

        $this->assertTrue($this->service->hasMinParticipantRequired($this->tournament));
    }

    public function testCanAddParticipant()
    {
        $this->tournament->setMaxParticipantNumber(3);
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);
        $this->assertTrue($this->service->canAddParticipant($this->tournament));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);
        $this->assertFalse($this->service->canAddParticipant($this->tournament));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);
        $this->tournament->addPlayer(new TFUser());
        $this->assertFalse($this->service->canAddParticipant($this->tournament));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);
        $this->assertFalse($this->service->canAddParticipant($this->tournament));
    }

    public function testCanBeStarted()
    {
        $owner = new TFUser();
        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);
        $this->tournament->setMaxParticipantNumber(4);
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->addPlayer(new TFUser());
        $this->tournament->addPlayer($owner);
        $this->tournament->setOwner($owner);
        $this->assertTrue($this->service->canBeStarted($this->tournament, $owner));

        $randomUser = new TFUser();
        $this->assertFalse($this->service->canBeStarted($this->tournament, $randomUser));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);
        $this->assertFalse($this->service->canBeStarted($this->tournament, $owner));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);
        $this->tournament->removePlayer($owner);
        $this->assertFalse($this->service->canBeStarted($this->tournament, $owner));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);
        $this->assertFalse($this->service->canBeStarted($this->tournament, $randomUser));
    }

    public function testIsStarted()
    {
        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);
        $this->assertTrue($this->service->isStarted($this->tournament));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);
        $this->assertFalse($this->service->isStarted($this->tournament));
    }

    public function testIsOwner()
    {
        $owner = new TFUser();
        $this->tournament->setOwner($owner);
        $this->assertTrue($this->service->isOwner($this->tournament, $owner));

        $randomUser = new TFUser();
        $this->tournament->setOwner($owner);
        $this->assertFalse($this->service->isOwner($this->tournament, $randomUser));
    }

    public function testIsInSetup()
    {
        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);
        $this->assertTrue($this->service->isInSetup($this->tournament));

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);
        $this->assertFalse($this->service->isInSetup($this->tournament));
    }
}
