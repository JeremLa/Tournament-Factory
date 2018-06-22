<?php

namespace App\Entity\Abstraction;

use App\Entity\TFTournament;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractTFParticipant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    public function __construct()
    {
        $this->tournaments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return TFTournament
     */
    public function getTournaments(): TFTournament
    {
        return $this->tournaments;
    }

    /**
     * @param TFTournament $tournament
     */
    public function addTournament(AbstractTFParticipant $tournament)
    {
        $this->tournaments->add($tournament);
    }

    /**
     * @param TFTournament $tournament
     */
    public function removeTournament(AbstractTFParticipant $tournament)
    {
        $this->tournaments->removeElement($tournament);
    }


}
