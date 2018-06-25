<?php

namespace App\Entity;

use App\Entity\Abstraction\AbstractTFParticipant;
use App\Services\Enum\TournamentStatusEnum;
use App\Services\Enum\TournamentTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\TFTournamentRepository")
 */
class TFTournament
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @ORM\Column(name="max_participant", type="integer", nullable=true)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Range(min = 0, max=9999, minMessage="{{ 'form.error.tournament.min-participant' | trans }}")
     */
    private $maxParticipantNumber;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    private $status;

    /**
     * @var TFUser[] | Collection $players
     * @ORM\ManyToMany(targetEntity="App\Entity\TFUser", mappedBy="tournaments")
     */
    private $players;

    /**
     * @var TFTeam[] | Collection $teams
     * @ORM\ManyToMany(targetEntity="App\Entity\TFTeam", mappedBy="tournaments")
     */
    private $teams;

    /**
     * @var TFUser $owner
     * @ORM\ManyToOne(targetEntity="App\Entity\TFUser", inversedBy="ownedtournaments")
     */
    private $owner;



    public function __construct(string $type)
    {
        $this->type = $type;
        $this->players = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->status = TournamentStatusEnum::STATUS_SETUP;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getMaxParticipantNumber()
    {
        return $this->maxParticipantNumber;
    }

    /**
     * @param mixed $maxParticipantNumber
     */
    public function setMaxParticipantNumber($maxParticipantNumber): void
    {
        $this->maxParticipantNumber = $maxParticipantNumber;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if (!in_array($type, TournamentTypeEnum::getAvailableTypes())) {
            throw new \InvalidArgumentException("Invalid type");
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return TFUser[] | Collection
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @param TFUser $participant
     */
    public function addPlayer(TFUser $participant)
    {
        $this->players->add($participant);
    }

    /**
     * @param TFUser $participant
     */
    public function removePlayer(TFUser $participant)
    {
        $this->players->removeElement($participant);
    }

    /**
     * @return TFTeam[] | Collection
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * @param TFTeam $participant
     */
    public function addTeam(TFTeam $participant)
    {
        $this->teams->add($participant);
    }

    /**
     * @param TFTeam $participant
     */
    public function removeTeam(TFTeam $participant)
    {
        $this->teams->removeElement($participant);
    }

    /**
     * @return TFUser
     */
    public function getOwner(): ?TFUser
    {
        return $this->owner;
    }

    /**
     * @param TFUser $owner
     */
    public function setOwner(TFUser $owner): void
    {
        $this->owner = $owner;
    }
}
