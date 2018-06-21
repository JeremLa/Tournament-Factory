<?php

namespace App\Entity;

use App\Entity\Abstraction\AbstractTFParticipant;
use App\Services\Enum\TournamentTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
     */
    private $name;

    /**
     * @ORM\Column(name="max_participant", type="integer", nullable=true)
     */
    private $maxParticipantNumber;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @var AbstractTFParticipant $participants
     * @ORM\ManyToMany(targetEntity="App\Entity\Abstraction\AbstractTFParticipant", mappedBy="tournaments")
     */
    private $participants;



    public function __construct()
    {
        $this->participants = new ArrayCollection();
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
     * @return AbstractTFParticipant
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param AbstractTFParticipant $participant
     */
    public function addParticipant(AbstractTFParticipant $participant)
    {
        $this->participants->add($participant);
    }

    /**
     * @param AbstractTFParticipant $participant
     */
    public function removeParticipanr(AbstractTFParticipant $participant)
    {
        $this->participants->removeElement($participant);
    }
}
